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
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div id="wrap">
<div class="container">
    <div class="jumbotron">
        <h1>Comandes</h1>
        <h2>Unitat de Convivència: <?php echo $descrip; ?></h2>
    </div>
    <?php echo "<a class='btn btn-success btn-block' href='comanda_new.php?uf=".$uf."' >Comanda Actual</a>" ?>
    <?php echo "<a class='btn btn-primary btn-block' href='userlist.php?uf=".$uf."' >Històric Comandes</a>" ?>
    <a class="btn btn-secondary btn-block" href="">Dades UC</a>
    <a class="btn btn-link btn-block" href="logout.php">Sortir</a>
</div>
</div>
<?php $conn->close(); ?>

<?php } else {
    header("Location: index.php");
}?>

</body>
</html>
