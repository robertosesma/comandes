<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova comanda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $uf = $_SESSION['username'];
    $fecha = getnext();
    $open = isopen();

    $conn = connect();
    $descrip = getdescrip($conn,$uf);

    // get UC comanda
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();

    $uctotal = gettotal($conn,$uf,$fecha);
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="jumbotron" "jumbotron-fluid">
    <?php $url = 'userlist.php?uf='.$uf; ?>
    <div class="container">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>Unitat de Convivència: <?php echo $descrip; ?></h2>
        <h3>Total: <?php echo $uctotal; ?></h3>
        <p>Aquest total no inclou alguns productes de preu variable</p>
        <?php if (!$open) {
            echo "<h2 class='text-warning'>Comanda tancada</h2>";
            echo '<a class="btn btn-link" href="export.php">Exportar</a>';
        } else {
            echo "<a class='btn btn-link' href='new_item.php?&fecha=".$fecha."'>Afegir producte</a>";
        }?>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="listaCDs">
        <thead class="thead-dark">
            <tr>
                <th>Productor</th>
                <th>Producte</th>
                <th><div class='text-center'>Quantitat</div></th>
                <th><div class='text-right'>Preu</div></th>
                <th><div class='text-right'>Total</div></th>
                <?php if ($open) { ?><th><div class='text-right'></div></th><?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) { ?>
            <?php
            $preu = ($row["precio"]==NULL ? '' : number_format($row["precio"], 2, ",", ".")."€");
            $tot = ($row["total"]==NULL ? '' : number_format($row["total"], 2, ",", ".")."€");
            $del = 'delete.php?&fecha='.$fecha.'&item='.$row["tipo"];
            ?>
            <tr>
                <td><?php echo $row["dgrupo"]; ?></td>
                <td><?php echo $row["item"]; ?></td>
                <td><div class='text-center'><?php echo $row["n"]; ?></div></td>
                <td><div class='text-right'><?php echo $preu; ?></div></td>
                <td><div class='text-right'><?php echo $tot; ?></div></td>
                <?php if ($open) { echo "<td><a onClick=\"javascript: return confirm('Si us plau, confirma que vols esborrar');\" href='".$del."'>x</a></td><tr>"; } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>

</body>
</html>
