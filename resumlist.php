<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Resum i Llistats</title>
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

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comandes GROUP BY fecha ORDER BY fecha DESC");
    $stmt->execute();
    $com = $stmt->get_result();
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron">
        <h1>Resum i Llistats</h1>
        <p>Cooperativa de Consum i Resistència Terrassa</p>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="comandes">
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) {
            $fecha = $row["fecha"];
            $resum = 'resum.php?&fecha='.$fecha;
            $detall = 'llistat.php?&fecha='.$fecha; ?>
            <tr>
                <td><?php echo $fecha; ?></td>
                <td><?php echo "<a href='".$resum."'>Resum</a>"; ?></td>
                <td><?php echo "<a href='".$detall."'>Llistat</a>"; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </tbody>
    </table>
</div>
<?php $conn->close(); ?>
<?php } else {
    header("Location: index.php");
}?>

</body>
</html>
