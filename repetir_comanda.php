<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true &&
    isset($_SESSION['username']) && isset($_SESSION['fecha'])) {
    $uf = $_SESSION['username'];
    $fecha = $_SESSION['fecha'];

    $conn = connect();
    // obtenir les comandes de la UF
    $stmt = $conn -> prepare("SELECT * FROM comandes WHERE uf=? ORDER BY fecha DESC");
    $stmt->bind_param('i',$uf);
    $stmt->execute();
    $comandes = $stmt->get_result();
    if ($comandes->num_rows == 0) {
        echo '<script>alert("No hi ha comanda anterior!")</script>';
    } else {
        $j = mysqli_fetch_array($comandes);
        if ($j['fecha'] == $fecha) {
            // la comanda actual ja existeix
            echo '<script>alert("La comanda actual conté ítems")</script>';
        } else {
            // afegir la nova comanda
            $stmt = $conn -> prepare("INSERT INTO comandes (fecha,uf) VALUES (?,?)");
            $stmt->bind_param('si',$fecha,$uf);
            $stmt->execute();

            // obtenir les dades de la comanda anterior
            $stmt = $conn -> prepare("SELECT * FROM items WHERE fecha=? AND uf=?");
            $stmt->bind_param('si',$j["fecha"],$uf);
            $stmt->execute();
            $dades = $stmt->get_result();
            // afegim els productes de la comanda anterior
            while ($r = mysqli_fetch_array($dades)) {
                $stmt = $conn -> prepare("SELECT dtipo.tipo, dtipo.descrip, dtipo.desactivado, dgrupo.activado
                                          FROM dtipo LEFT JOIN dgrupo ON (dtipo.grupo = dgrupo.cod)
                                          WHERE dtipo.tipo=?");
                $stmt->bind_param('i',$r["tipo"]);
                $stmt->execute();
                $act = $stmt->get_result();
                $k = mysqli_fetch_array($act);
                if ($k["desactivado"]==0 && $k["activado"]==1) {
                    $stmt = $conn -> prepare("INSERT INTO items (fecha,uf,tipo,n) VALUES (?,?,?,?)");
                    $stmt->bind_param('siii',$fecha,$uf,$r["tipo"],$r["n"]);
                    $stmt->execute();
                }
                $act->free();
            }
            $dades->free();
        }
        $comandes->free();
    }
    $conn->close();
    echo '<script>window.location.href = "new_comanda.php";</script>';
}
?>
