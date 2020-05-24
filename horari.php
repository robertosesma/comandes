<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Horari</title>
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

    // obtenir llista d'UC i horaris
    $stmt = $conn -> prepare("SELECT uf.descrip AS d, c.hora AS h FROM comandes c
                            LEFT JOIN uf ON (uf.uf = c.uf)
                            WHERE fecha =? ORDER BY -hora DESC;");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $data = $stmt->get_result();
    $nuc = $data->num_rows;
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Horaris comanda <?php echo $fecha; ?></h1>
        <h2>Unitats de Convivència: <?php echo $nuc; ?></h2>
        <a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
    <tbody>
    <?php
    $count = 1;
    while ($r = mysqli_fetch_array($data)) { ?>
        <tr>
            <td><?php echo $count; ?></td>
            <td><?php echo $r["d"]; ?></td>
            <?php if (is_null($r["h"])) {
                echo "<td></td>";
            } else {
                echo "<td>".gethhmm($conn,$r["h"])."</td>";
            } ?>
        </tr>
    <?php
        $count++;
    } ?>
    </tbody>
    </table>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
