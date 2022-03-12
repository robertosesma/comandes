<?php session_start(); ?>

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
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $uf = $_SESSION['username'];
    $conn = connect();
    $descrip = getdescrip($conn,$uf);

    // és l'usuari administrador?
    $stmt = $conn -> prepare('SELECT * FROM uf WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $_SESSION["admin"] = $r["admin"];
    }
    $res->free();

    // és contacte de productor?
    $stmt = $conn -> prepare('SELECT * FROM dgrupo WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $res = $stmt->get_result();
    $nrows = $res->num_rows;
    $_SESSION["contacte"] = 0;
    if ($nrows>0) {
        $_SESSION["contacte"] = 1;
        while ($r = $res->fetch_assoc()) {
            $prod = $r["cod"];
        }
    }
    $res->free();

    // es poden fer comandes?
    $stmt = $conn -> prepare('SELECT * FROM admin');
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $act = $r["comanda_act"];
    }
    $res->free();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron">
        <h1>Comandes</h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <?php if ($act==0){ echo "<h2 class='text-warning'>No es possible fer noves comandes</h2>"; } ?>
    </div>
    <?php if ($act==1){ echo "<a class='btn btn-success btn-block' href='new_comanda.php'>Comanda actual</a>"; } ?>
    <a class='btn btn-primary btn-block' href='history.php'>Històric comandes</a>
    <a class='btn btn-info btn-block' href='https://docs.google.com/spreadsheets/d/1ELxhd3KJ8p5y4S3GIt5B6dIFghN7xlx6/' target="_blank" rel="noopener noreferrer">Calendari Obertures</a>
    <?php if ($_SESSION["admin"]==0) { echo "<a class='btn btn-secondary btn-block' href='edit_uc.php' >Dades UC</a>"; } ?>
    <?php if ($_SESSION["contacte"]==1){ echo "<a class='btn btn-secondary btn-block' href='llista_items.php?prod=".$prod."' >Editar productes</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin_uc.php'>Administrar UC</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin_prods.php'>Administrar productores</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin.php'>Administrar aplicació</a>"; } ?>
    <a class="btn btn-link btn-block" href="logout.php">Sortir</a>
</div>

<?php $conn->close(); ?>

<?php } else {
    header("Location: logout.php");
}?>

</body>
</html>
