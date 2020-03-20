<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true &&
    isset($_SESSION['username']) && isset($_GET['grupo']) &&
    isset($_GET['item']) && isset($_GET['desact'])) {
    $grupo = clear_input($_GET["grupo"]);
    $item = clear_input($_GET["item"]);
    $desactivado = clear_input($_GET["desact"]);
    $conn = connect();

    $stmt = $conn -> prepare("UPDATE dtipo SET desactivado = ? WHERE tipo = ?");
    $stmt->bind_param('ii', $desactivado, $item);
    $stmt->execute();
    echo '<script>window.location.href = "llista_items.php?grupo='.$grupo.'";</script>';
}
exit();
?>
