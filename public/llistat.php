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
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['fecha'])) {
    $fecha = $_GET["fecha"];
    $conn = connect();

    // obtenir llista d'UC
    $stmt = $conn -> prepare("SELECT uf, descrip FROM comanda WHERE fecha =? GROUP BY uf ORDER BY descrip");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $ucs = $stmt->get_result();
    $nuc = $ucs->num_rows;

    if ($nuc==0) {
        $ok = false;
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Llistat comanda <?php echo $fecha; ?></h1>
        <h2>Unitats de Convivència: <?php echo $nuc; ?></h2>
        <p>Els totals no inclouen alguns productes de preu variable</p>
        <?php echo '<a class="btn btn-link" href="llistat_rtf.php?&fecha='.$fecha.'">Descarregar</a>'; ?>
        <?php echo '<a class="btn btn-link" href="preuvar.php?&fecha='.$fecha.'">Preu var.</a>'; ?>
        <?php echo '<a class="btn btn-link" href="des_items.php?&fecha='.$fecha.'">Anul·lar prod.</a>'; ?>
        <a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
<?php $count = 1;
while ($r = mysqli_fetch_array($ucs)) {
    $uf = $r["uf"];

    echo "<h3>".$count.". ".$r["descrip"].": ".gettotal($conn,$uf,$fecha)."</h3>";

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();
    $open = false;
    include 'comanda_tbl.php';

    $count++;
    $row = $row + 2;
} ?>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
