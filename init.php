<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Comandes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="carxofa.png"/>
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $uf = $_SESSION['username'];
    $conn = connect();
    $descrip = getdescrip($conn,$uf);

    // dades uf?
    $stmt = $conn -> prepare('SELECT * FROM uf WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $res = $stmt->get_result();
    $r = $res->fetch_assoc();
    // és administrador?
    $_SESSION["admin"] = $r["admin"];
    // és tresorera?
    $_SESSION["tresorer"] = $r["tresorer"];
    // és calendari?
    $_SESSION["calendari"] = $r["calendari"];
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

    // hi ha membres definits?
    $stmt = $conn -> prepare('SELECT * FROM membres WHERE uf=?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $res = $stmt->get_result();
    $nmembres = $res->num_rows;
    $res->free();

    // s'ha pagat la quota?
    $year = date("Y");
    $stmt = $conn -> prepare('SELECT * FROM quotes WHERE uf=? AND year=?');
    $stmt->bind_param('ii', $uf, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    $nquotes = $res->num_rows;
    $res->free();

    // data actual i de referència per decidir si s'ha de mostrar l'avís
    $date_now = new DateTime();
    $date_ref = new DateTime(getyend()."/01/20");
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron" style="margin-top:5px"></div>
    <div style="margin-top:10px">
        <h2>UC: <?php echo $descrip; ?></h2>
        <?php if ($nquotes==0 && $date_now>$date_ref){ echo "<h2 class='text-danger'>Quota any ".$year." pendent</h2>"; } ?>
        <?php if ($nmembres==0){ echo "<h2 class='text-warning'>No hi ha membres definits</h2>";
        echo "<h4>Si us plau, utilitzeu el botó Dades UC per completar la informació</h4>"; } ?>
        <?php if ($act==0){ echo "<h2 class='text-warning'>No es possible fer noves comandes</h2>"; } ?>
    </div>
    <?php if ($act==1){ echo "<a class='btn btn-success btn-block' href='new_comanda.php'>Comanda actual</a>"; } ?>
    <a class='btn btn-primary btn-block' href='history.php'>Històric comandes</a>
    <a class='btn btn-info btn-block' href='https://docs.google.com/spreadsheets/d/1ELxhd3KJ8p5y4S3GIt5B6dIFghN7xlx6/' target="_blank" rel="noopener noreferrer">Calendari Obertures</a>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-info btn-block' href='calendari.php'>Calendari</a>"; } ?>
    <?php if ($_SESSION["admin"]==0) { echo "<a class='btn btn-secondary btn-block' href='edit_uc.php?add=0' >Dades UC</a>"; } ?>
    <?php if ($_SESSION["contacte"]==1){ echo "<a class='btn btn-secondary btn-block' href='llista_items.php?prod=".$prod."' >Editar productes</a>"; } ?>
    <?php if ($_SESSION["admin"]==1 || $_SESSION["tresorer"]==1){ echo "<a class='btn btn-secondary btn-block' href='quotes.php' >Quotes</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin_uc.php'>Administrar UC</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin_prods.php'>Administrar productores</a>"; } ?>
    <?php if ($_SESSION["admin"]==1){ echo "<a class='btn btn-secondary btn-block' href='admin.php'>Administrar aplicació</a>"; } ?>
    <a class='btn btn-secondary btn-block' href='llistat_uc.php'>Llistat UC</a>
    <a class="btn btn-link btn-block" href="logout.php">Sortir</a>
</div>

<?php $conn->close(); ?>

<?php } else {
    header("Location: logout.php");
}?>

</body>
</html>
