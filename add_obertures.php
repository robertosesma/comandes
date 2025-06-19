<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && 
    ((isset($_SESSION['admin']) && $_SESSION['admin'] == true) || 
     (isset($_SESSION['calendari']) && $_SESSION['calendari'] == true))) {
        $conn = connect();

        // obtenir l'última data i l'última uf del calendari
        $stmt = $conn -> prepare("SELECT c.fecha, c.uc2, u.descrip AS desc2
            FROM calendari c 
            LEFT JOIN uf u on (u.uf = c.uc2)
            ORDER BY fecha DESC LIMIT 1;");
        $stmt->execute();
        $res = $stmt->get_result();
        $r = $res->fetch_assoc(); 
        $fecha = $r["fecha"];
        $uc1 = $r["uc2"];       // la UC2 de l'última obertura és la UC1 del nou cicle
        $res->free();
        
        // obtenir les uf per ordre alfabètic
        $stmt = $conn -> prepare("SELECT uf, descrip FROM uf WHERE act = 1 AND obertura = 1 AND uf < 10000 ORDER BY descrip;");
        $stmt->execute();
        $uf = $stmt->get_result();
        // bucle per les uf per afegir noves dates al calendari
        while ($u = mysqli_fetch_array($uf)) {
            // la uc actual és la 2ª en la iteració actual
            $uc2 = $u["uf"];
            $stmt = $conn -> prepare("INSERT INTO calendari (fecha, uc1, uc2, cerrado, asamblea, coment)
                    VALUES (?,?,?,0,0,NULL)");
            $stmt->bind_param('sii', $fecha, $uc1, $uc2);
            $stmt->execute();
            // la uc actual és la 1ª en la següent iteració
            $uc1 = $uc2;
            // afegir 7 dies a la data
            $fecha = date('Y-m-d', strtotime($fecha.' + 7 days'));
        }
        $uf->free();
        $conn->close();

        echo '<script>window.location.href = "calendari.php";</script>';
}
exit();
?>