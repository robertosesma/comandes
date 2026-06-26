<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && 
    ((isset($_SESSION['admin']) && $_SESSION['admin'] == true) || 
     (isset($_SESSION['calendari']) && $_SESSION['calendari'] == true))) {
    $conn = connect();

    try {
        // defecte per la próxima comanda
        $stmt = $conn -> prepare("SELECT next FROM admin");
        $stmt->execute();
        $d = $stmt->get_result();
        $r = $d->fetch_assoc();
        $next = $r["next"];
        $d->free();

        // obtenir l'última data i l'última uf del calendari
        $stmt = $conn -> prepare("SELECT c.fecha, c.uc2, u.descrip AS desc2
            FROM calendari c 
            LEFT JOIN uf u on (u.uf = c.uc2)
            ORDER BY fecha DESC LIMIT 1;");
        $stmt->execute();
        $res = $stmt->get_result();
        $r = $res->fetch_assoc(); 
        $fecha = $r["fecha"];
        $uc1 = $r["uc2"];       // la UC2 de l'última obertura és la UC1 de la nova
        $res->free();

        // dia de la propera comanda
        $date0 = new DateTime($fecha);
        $date = new DateTime($fecha);
        $date->modify($next);       // modificar per obtenir la propera data
        // corregir si la nova data és a la mateixa setmana
        $diff = $date0->diff($date);
        if ($diff->days<6) $date->modify($next);
        $fecha = $date->format('Y-m-d');

        
        // afegir la nova obertura
        $stmt = $conn -> prepare("INSERT INTO calendari (fecha, uc1, uc2, cerrado, asamblea, coment)
                VALUES (?,?,NULL,0,0,NULL)");
        $stmt->bind_param('si', $fecha, $uc1);
        $stmt->execute();
        $conn->close();

        echo '<script>window.location.href = "calendari.php";</script>';
    } catch (Exception $e) {
        echo 'Mensaje de error: ',  $e->getMessage(), "\n";
    }
}
// exit();
?>