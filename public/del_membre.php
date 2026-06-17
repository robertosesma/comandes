<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    if (isset($_GET['n'])) {
        $uf = $_SESSION['username'];
        $n = clear_input($_GET["n"]);
        // esborrar el membre
        $conn = connect();
        $stmt = $conn -> prepare("DELETE FROM membres WHERE uf=? AND n=?");
        $stmt->bind_param('ii',$uf,$n);
        $stmt->execute();
        echo '<script>window.location.href = "edit_uc.php?add=0";</script>';
    }
}
exit();
?>
