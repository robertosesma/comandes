<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar UC</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && isset($_SESSION['admin']) && ($_SESSION['admin']==1)) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $dini = clear_input($_POST["dini"]);
        $hini = clear_input($_POST["hini"]);
        $dend = clear_input($_POST["dend"]);
        $hend = clear_input($_POST["hend"]);
        $act = (clear_input($_POST["act"])=="activado");
        $act = ($act == 1 ? 1 : 0);
        $next = clear_input($_POST["next"]);
        $next = str_replace("_"," ",$next);
        $stmt = $conn -> prepare("UPDATE admin SET dini=?, hini=?, dend=?, hend=?, next=?, comanda_act=? WHERE id=1");
        $stmt->bind_param('iiiisi', $dini, $hini, $dend, $hend, $next, $act);
        $stmt->execute();
        header("Location: init.php");
    }

    // carregar dades administració
    $stmt = $conn -> prepare("SELECT * FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    while ($r = mysqli_fetch_array($dades)) {
        $dini = $r["dini"];
        $hini = $r["hini"];
        $dend = $r["dend"];
        $hend = $r["hend"];
        $next = $r["next"];
        $next = str_replace(" ","_",$next);
        $act = $r["comanda_act"];
    }
    $days = array("Dilluns" => 1, "Dimarts" => 2, "Dimecres" => 3, "Dijous" => 4,
            "Divendres" => 5, "Dissabte" => 6, "Diumenge" =>7 );
    $days_next = array("Dilluns" => "next_monday", "Dimarts" => "next_tuesday",
            "Dimecres" => "next_wednesday", "Dijous" => "next_thursday",
            "Divendres" => "next_friday", "Dissabte" => "next_saturday",
            "Diumenge" => "next_sunday" );
} else {
    header("Location: logout.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Administrar aplicació</h2>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="dini">Dia inici comanda:</label>
            <select name="dini" class="custom-select" required>
            <?php foreach ($days as $k => $v) {
                if ($v==$dini) {
                    echo '<option value='.$v.' selected>'.$k.'</option>';
                } else {
                    echo '<option value='.$v.'>'.$k.'</option>';
                }
            } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="hini">Hora inici comanda:</label>
            <input type="number" min="0" max="24" step="1" class="form-control" name="hini"
             value="<?php echo $hini;?>" required>
        </div>
        <div class="form-group">
            <label for="dend">Dia final comanda:</label>
            <select name="dend" class="custom-select" required>
            <?php foreach ($days as $k => $v) {
                if ($v==$dend) {
                    echo '<option value='.$v.' selected>'.$k.'</option>';
                } else {
                    echo '<option value='.$v.'>'.$k.'</option>';
                }
            } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="hend">Hora final comanda:</label>
            <input type="number" min="0" max="24" step="1" class="form-control" name="hend"
             value="<?php echo $hend;?>" required>
        </div>
        <div class="form-group">
            <label for="dend">La comanda es fa el proper:</label>
            <select name="next" class="custom-select" required>
            <?php foreach ($days_next as $k => $v) {
                if ($v==$next) {
                    echo '<option value='.$v.' selected>'.$k.'</option>';
                } else {
                    echo '<option value='.$v.'>'.$k.'</option>';
                }
            } ?>
            </select>
        </div>
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="act" id="act"
                    value="activado" <?php echo ($act==1 ? "checked" : ""); ?>>
                <label class="custom-control-label" for="act">Comandes actives</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php $conn->close(); ?>

</body>
</html>
