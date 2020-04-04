<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Comandes registre</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
// define variables and set to empty values
$pswdErr = "";
$user = $pswd = "";

$conn = connect();

// get UF data for UF drop-down
$stmt = $conn -> prepare('SELECT * FROM uf WHERE act=1 ORDER BY descrip');
$stmt->execute();
$duf = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = clear_input($_POST["uf"]);
    if (empty($_POST["pswd"])) {
        $pswdErr = "Indicar una contrasenya";
    } else {
        $pswd = clear_input($_POST["pswd"]);

        $stmt = $conn -> prepare('SELECT psswd FROM uf WHERE uf = ?');
        $stmt->bind_param('i', $user);
        $stmt->execute();
        $users = $stmt->get_result();
        $nrows = $users->num_rows;
        $_SESSION['loggedin'] = true;
        $_SESSION['username']=$user;
        if ($nrows > 0) {
            while($r = $users->fetch_assoc()) {
                 if (password_verify($pswd, $r["psswd"])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username']=$user;
                    if ($user < getmax($conn)) {
                        header("Location: init.php");
                    } else {
                        header("Location: productora.php");
                    }
                } else {
                    $pswdErr = "La contrasenya no és correcta";
                }
            }
        }
    }
}
?>

<div class="container">
    <div class="jumbotron">
        <h1>comandes</h1>
        <p>Cooperativa de Consum i Resistència Terrassa</p>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="uf">Unitat de convivència:</label>
            <select name="uf" class="custom-select">
                <option selected></option>
                <?php while ($r = mysqli_fetch_array($duf)) {
                    echo "<option value=".$r["uf"].">".$r["descrip"]."</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="pswd">Contrasenya:</label>
            <input type="password" class="form-control" name="pswd">
            <span class="error text-danger"><?php echo $pswdErr;?></span>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</div>

<?php $conn->close(); ?>

</body>
</html>
