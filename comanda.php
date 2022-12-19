<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Detall comanda</title>
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
    $horari_act = ishorari_act($conn);

    $reload = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($horari_act) {
            $fecha = clear_input($_POST["fecha"]);
            $hora = clear_input($_POST["hour"]);
            // comprovar que l'hora no està agafada
            $stmt = $conn -> prepare("SELECT * FROM comandes WHERE uf!=? AND fecha=? AND hora=?");
            $stmt->bind_param('isi', $uf, $fecha, $hora);
            $stmt->execute();
            $check = $stmt->get_result();
            $nrows = $check->num_rows;
            if ($nrows == 0) {
                // gravar l'hora
                $stmt = $conn -> prepare("UPDATE comandes SET hora=? WHERE fecha=? AND uf=?");
                $stmt->bind_param('isi', $hora, $fecha, $uf);
                $stmt->execute();
                header("Location: horari.php?&fecha=".$fecha);
            } else {
                echo "<script type='text/javascript'>alert('Hora no disponible, escull una diferent');</script>";
            }
            $reload = true;
        }
    }
    if (($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fecha'])) || $reload) {
        $fecha = ($reload ? $fecha : $_GET["fecha"]);

        // get UC comandes
        $stmt = $conn -> prepare("SELECT * FROM comanda WHERE uf = ? AND fecha = ?");
        $stmt->bind_param('is', $uf, $fecha);
        $stmt->execute();
        $com = $stmt->get_result();
        $nrows = $com->num_rows;
        if ($nrows > 0) {
            $uctotal = gettotal($conn,$uf,$fecha);

            if ($horari_act) {
                // obtenir hora recollida de la UC
                $stmt = $conn -> prepare("SELECT * FROM comandes WHERE fecha =? AND uf=?");
                $stmt->bind_param('si', $fecha, $uf);
                $stmt->execute();
                $com_uf = $stmt->get_result();
                $r = mysqli_fetch_array($com_uf);
                $hora_uf = (is_null($r["hora"]) ? 0 : $r["hora"]);
                $com_uf->free();

                // obtenir nombre TOTAL de comandes (per calcular les hores)
                $stmt = $conn -> prepare("SELECT * FROM comandes WHERE fecha =?");
                $stmt->bind_param('s', $fecha);
                $stmt->execute();
                $comandes = $stmt->get_result();
                $ncom = $comandes->num_rows;
                $comandes->free();

                // html del combo d'hores
                $items_hores = "<option value=0></option>";
                // obtenir la llista d'hores demanades en forma d'array per no mostrar-les
                $stmt = $conn -> prepare("SELECT hora FROM comandes WHERE fecha=?");
                $stmt->bind_param('s', $fecha);
                $stmt->execute();
                $hores = $stmt->get_result();
                $res = array();
                while ($h = mysqli_fetch_array($hores)) {
                    $res[] = $h["hora"];
                }
                $hores->free();
                // carregar el combo amb les hores lliures i la demanada, si és el cas
                for ($id=1; $id<=$ncom; $id++) {
                    if ($id==$hora_uf || !array_search($id,$res,TRUE)) {
                        $sel = ($id==$hora_uf ? "selected" : "");
                        $items_hores .= "<option value=".$id." ".$sel.">".gethhmm($conn,$id)."</option>";
                    }
                }
            }
        }
    } else {
        $ok = false;
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Comanda <?php echo $fecha; ?></h1>
        <h2>UC: <?php echo $descrip; ?></h2>
        <h3>Total: <?php echo $uctotal; ?></h3>
        <p>Aquest total no inclou alguns productes de preu variable</p>
        <a class="btn btn-link" href="history.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

<?php
    if ($nrows > 0) {
        if ($horari_act) {?>
        <div class="container p-3 my-3 border">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="hour">Hora recollida:</label>
                    <select name="hour" id="hour" class="custom-select col-md-2">
                        <?php echo $items_hores; ?>
                    </select>
                </div>
                <input type="text" class="form-control" hidden="true" name="fecha" value=" <?php echo $fecha; ?> ">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
<?php }

        $open = false;
        include 'comanda_tbl.php';
    } else { ?>
        <div class="container">
            <h1 class="text-warning">No hi ha productes</h1>
        </div>
<?php }
    echo "</div>";

    $conn->close();
} else {
    // header("Location: logout.php");
}?>

</body>
</html>
