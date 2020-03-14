<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    if (isset($_GET['fecha']) && isset($_GET['item'])) {
        $uf = $_SESSION['username'];
        $fecha = $_GET["fecha"];
        $item = $_GET["item"];

        $conn = connect();
        $stmt = $conn -> prepare("DELETE FROM items WHERE fecha=? AND uf=? AND tipo=?");
        $stmt->bind_param('sii',$fecha,$uf,$item);
        $stmt->execute();

        // check if there are no items
        $stmt = $conn -> prepare("SELECT * FROM items WHERE fecha=?");
        $stmt->bind_param('s',$fecha);
        $stmt->execute();
        $items = $stmt->get_result();
        if ($items->num_rows == 0) {
            // if there are no items, delete comanda
            $stmt = $conn -> prepare("DELETE FROM comandes WHERE fecha=? AND uf=?");
            $stmt->bind_param('si',$fecha,$uf);
            $stmt->execute();
        }

        echo '<script>window.location.href = "comanda_new.php?uf='.$uf.'";</script>';
    }
}
exit();
?>
