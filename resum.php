<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Resum</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = $_GET["fecha"];
    $conn = connect();

    // get productors
    $stmt = $conn -> prepare("SELECT cod, descrip FROM dgrupo");
    $stmt->execute();
    $prods = $stmt->get_result();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Resum comanda <?php echo $fecha; ?></h1>
        <p>Els totals no inclouen alguns productes de preu variable</p>
        <a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <?php while ($r = mysqli_fetch_array($prods)) {
        $productor = $r["cod"];
        $descrip = $r["descrip"];

        include 'resum_prod.php';
    } ?>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
