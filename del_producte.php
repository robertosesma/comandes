<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    if (isset($_GET['item'])) {
        $uf = $_SESSION['username'];
        $fecha = $_SESSION['fecha'];
        $item = $_GET["item"];

        // esborrar el producte
        $conn = connect();
        $stmt = $conn -> prepare("DELETE FROM items WHERE fecha=? AND uf=? AND tipo=?");
        $stmt->bind_param('sii',$fecha,$uf,$item);
        $stmt->execute();

        // comprovar si la comanda no té cap producte
        $stmt = $conn -> prepare("SELECT * FROM items WHERE fecha=? AND uf=?");
        $stmt->bind_param('si',$fecha,$uf);
        $stmt->execute();
        $items = $stmt->get_result();
        if ($items->num_rows == 0) {
            // si no hi ha items, esborrar la comanda
            $stmt = $conn -> prepare("DELETE FROM comandes WHERE fecha=? AND uf=?");
            $stmt->bind_param('si',$fecha,$uf);
            $stmt->execute();
        }
        $items->free();
        echo '<script>window.location.href = "new_comanda.php";</script>';
    }
}
exit();
?>
