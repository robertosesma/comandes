<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Anul·lar</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && (isset($_GET['fecha']) OR isset($_POST['fecha']))) {
    $conn = connect();
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") $fecha = clear_input($_POST["fecha"]);
    else $fecha = $_GET["fecha"];

    // obtenir llistat d'items per la comanda
    $stmt = $conn -> prepare("SELECT i.fecha, i.tipo, t.grupo, d.descrip AS prod, t.descrip, i.desactivado
        FROM items i
        LEFT JOIN dtipo t ON i.tipo = t.tipo
        LEFT JOIN dgrupo d ON t.grupo = d.cod
        WHERE fecha = ?
        GROUP by fecha, tipo, grupo, d.descrip, t.descrip, desactivado
        ORDER BY grupo, descrip");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $items = $stmt->get_result();
    $nitems = $items->num_rows;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
         // desactivar productes
         foreach ($items as $r) {
            $des = 0;
            if (strtolower(clear_input($_POST["it".$r['tipo']])) === "on") $des = 1;
            $stmt = $conn -> prepare("UPDATE items SET desactivado = ? WHERE fecha=? AND tipo=?");
            $stmt->bind_param('isi',$des,$fecha,$r['tipo']);
            $stmt->execute();
        }
        header("Location: llistat.php?fecha=".$fecha);
    }
} else {
    $ok = false;
}

if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h3>Anul·lar productes</h3>
        <h4>Comanda: <?php echo $fecha; ?></h4>
        <h5>Marcar els productes a anul·lar</h5>
        <?php echo '<a class="btn btn-link" href="llistat.php?fecha='.$fecha.'">Tornar</a>'; ?>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<?php if ($nitems > 0) { ?>
    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <button type="submit" class="btn btn-primary" style="margin-top:5px">Enviar</button>
        <?php $prod = ""; 
        foreach ($items as $r) { 
            if ($prod != $r["prod"]) {
                $prod = $r["prod"];
                echo '<h3>'.$prod.'</h3>';
            } else {
                $it = 'it'.$r["tipo"];
                $desc = $r["descrip"];
                $checked = ($r["desactivado"]==1 ? "checked" : "");
                echo "<div class='form-group'>";
                echo "<label><input type='checkbox' name='$it' ".$checked."> $desc</label>";
                echo "</div>";
            }
        } ?>
        <input type="text" class="form-control" hidden="true" name="fecha" value=" <?php echo $fecha; ?> ">
        </form>
    </div>
<?php } else { ?>
    <div class="container">
        <h2>No hi ha productes</h2>
    </div>
<?php }
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
