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
        $max = $r["max_uf"];
    }
    $days = array("Dilluns" => 1, "Dimarts" => 2, "Dimecres" => 3, "Dijous" => 4,
            "Divendres" => 5, "Dissabte" => 6, "Diumenge" =>7 );
} else {
    header("Location: index.php");
}
?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Administrar</h2>
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
            <label for="hend">Hora inici comanda:</label>
            <input type="number" min="0" max="24" step="1" class="form-control" name="hend"
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
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php $conn->close(); ?>

</body>
</html>
