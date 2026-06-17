<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Preu variable</title>
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
    $fecha = $_GET["fecha"];
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fecha = clear_input($_POST["fecha"]); 
        foreach($_POST as $key => $val) {
            $key = clear_input($key);
            $val = clear_input($val);
            if ($key!="fecha") {
                $item = str_replace("it","",$key);
                $preu = (strlen($val)>0 ? str_replace(",",".",$val) : NULL);
                $stmt = $conn -> prepare("UPDATE items SET preuvar=? WHERE fecha=? AND tipo=?");
                $stmt->bind_param('dsi', $preu, $fecha, $item);
                $stmt->execute();
            }
        }
        header("Location: llistat.php?fecha=".$fecha);
    }

    // obtenir llistat d'items de preu variable per la comanda
    $stmt = $conn -> prepare("SELECT i.fecha, i.tipo, i.preuvar, t.descrip, t.precio
        FROM items i
        LEFT JOIN dtipo t ON i.tipo = t.tipo
        WHERE fecha = ? AND (ISNULL(precio) OR precio=0)
        GROUP by fecha, tipo, preuvar, descrip, precio");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $items = $stmt->get_result();
    $nitems = $items->num_rows;
} else {
    $ok = false;
}

if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Assignar preus variables</h1>
        <h2>Comanda: <?php echo $fecha; ?></h2>
        <?php echo '<a class="btn btn-link" href="llistat.php?fecha='.$fecha.'">Tornar</a>'; ?>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<?php if ($nitems > 0) { ?>
    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <?php foreach ($items as $r) {
            $descrip = $r["descrip"]; 
            $it = 'it'.$r["tipo"];
            $preuvar = (is_null($r["preuvar"]) ? "" : $r["preuvar"]);
            echo "<div class='form-group'>";
            echo "<label for='$it'>$descrip (€):</label>";
            echo "<input type='number' class='form-control' min=0 step='.01' name='$it' value='$preuvar'>";
            echo "</div>";
        } ?>
        <input type="text" class="form-control" hidden="true" name="fecha" value=" <?php echo $fecha; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>

<?php } else { ?>
    <div class="container">
        <h2>No hi ha productes de preu variable</h2>
    </div>
<?php }
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
