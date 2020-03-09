<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comandes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && (isset($_GET['uf']) || $_SERVER["REQUEST_METHOD"] == "POST")) {
    $uf = $_GET["uf"];

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

    // get UC comandes
    $stmt = $conn -> prepare("SELECT * FROM comandes WHERE uf = ? ORDER BY fecha DESC");
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $com = $stmt->get_result();
    $ok = ($com->num_rows > 0);

} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div id="wrap">
<div class="container">
    <?php $url = 'init.php?uf='.$uf; ?>
    <div class="jumbotron">
        <h1>Històric de Comandes</h1>
        <p>Cooperativa de Consum i Resistència Terrassa</p>
        <h2>Unitat de Convivència: <?php echo $descrip; ?></h2>
        <a class="btn btn-link" href=<?php echo $url; ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered" id="comandes">
        <tbody>
        <?php while ($row = mysqli_fetch_array($com)) { ?>
            <?php
            $fecha = $row["fecha"];
            $url = 'comanda.php?uf='.$uf.'&fecha='.$fecha;
            ?>
            <tr>
                <td><?php echo "<a href='".$url."'>".$fecha."</a>"; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</div>
<?php $conn->close(); ?>

<?php } else {
    header("Location: index.php");
}?>

</body>
</html>
