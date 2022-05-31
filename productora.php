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
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $fecha = getnext($conn);
    $user = $_SESSION['username'];
    $descrip = getdescrip($conn,$user);
    $productor = $user - getmax($conn);
    $open = isopen($conn);
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>Resum</h2>
        <?php if ($open) {
            echo "<h2 class='text-warning'>ATENCIÓ!! La comanda encara està oberta</h2>";
			echo "<h2 class='text-warning'>Les dades no són DEFINITIVES</h2>";
        }
		echo "<p>Els totals no inclouen alguns productes de preu variable</p>";
        if ($productor == 4 && !$open) {
            echo '<a class="btn btn-link" href="pa.php?&fecha='.$fecha.'">Descarregar Excel</a>';
        }?>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
<?php include 'resum_prod.php'; ?>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
