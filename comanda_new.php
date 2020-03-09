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
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && (isset($_GET['uf']) || $_SERVER["REQUEST_METHOD"] == "POST")) {
    $uf = $_GET["uf"];
    // the actual comanda is always the next tuesday
    $fecha = date('Y-m-d',strtotime('next tuesday'));
    // is there an open comanda?
    $day = date("N");
    $hour = date("H");
    $open = (($day==2 and $hour>=10) or $day==3 or $day==4 or ($day==5 and $hour<=18));
    $open = true;

    // Create connection
    $conn = new mysqli("localhost", "rsesma", "Amsesr.1977", "comandes");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->query("SET NAMES 'utf8'");
    $conn->query("SET CHARACTER SET utf8");
    $conn->query("SET SESSION collation_connection = 'utf8_unicode_ci'");

    // get UC descrip
    $stmt = $conn -> prepare('SELECT descrip FROM uf WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $users = $stmt->get_result();
    if ($users->num_rows > 0) {
        while($r = $users->fetch_assoc()) {
            $descrip = $r["descrip"];
        }
    }

    // get UC comanda
    $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $com = $stmt->get_result();

    // get total for UC and comanda
    $stmt = $conn -> prepare("SELECT Sum(total) AS total FROM comanda WHERE uf = ? AND fecha = ? GROUP BY fecha, uf");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $totals = $stmt->get_result();
    if ($totals->num_rows > 0) {
        while($r = $totals->fetch_assoc()) {
            $uctotal = ($r["total"]==NULL ? '' : number_format($r["total"], 2, ",", ".")."€");
        }
    }
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
        } else {
            echo "<a class='btn btn-link' href='new_item.php?uf=".$uf."&fecha=".$fecha."'>Afegir producte</a>";
        }?>
        <a class="btn btn-link" href=<?php echo "init.php?uf=".$uf; ?>>Tornar</a>
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
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) { ?>
            <?php
            $preu = ($row["precio"]==NULL ? '' : number_format($row["precio"], 2, ",", ".")."€");
            $tot = ($row["total"]==NULL ? '' : number_format($row["total"], 2, ",", ".")."€");
            ?>
            <tr>
                <td><?php echo $row["dgrupo"]; ?></td>
                <td><?php echo $row["item"]; ?></td>
                <td><div class='text-center'><?php echo $row["n"]; ?></div></td>
                <td><div class='text-right'><?php echo $preu; ?></div></td>
                <td><div class='text-right'><?php echo $tot; ?></div></td>
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
