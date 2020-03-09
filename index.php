<?php
session_start();
?>

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
// define variables and set to empty values
$pswdErr = "";
$user = $pswd = "";

// Create connection
$conn = new mysqli("localhost", "rsesma", "Amsesr.1977", "comandes");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET NAMES 'utf8'");
$conn->query("SET CHARACTER SET utf8");
$conn->query("SET SESSION collation_connection = 'utf8_unicode_ci'");

// get UF data for UF drop-down
$stmt = $conn -> prepare('SELECT * FROM uf');
$stmt->execute();
$duf = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = clear_input($_POST["uf"]);
    if (empty($_POST["pswd"])) {
        $pswdErr = "La contraseña es necesaria";
    } else {
        $pswd = clear_input($_POST["pswd"]);

        $stmt = $conn -> prepare('SELECT psswd FROM uf WHERE uf = ?');
        $stmt->bind_param('i', $user);
        $stmt->execute();
        $users = $stmt->get_result();
        $nrows = $users->num_rows;
        if ($nrows > 0) {
            while($r = $users->fetch_assoc()) {
                if (password_verify($pswd, $r["psswd"])) {
                    $_SESSION['loggedin'] = true;
                    echo '<script>window.location.href = "init.php?uf='.$user.'";</script>';
                } else {
                    $pswdErr = "La contrassenya no és correcta";
                }
            }
        }
    }
}

function clear_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
                echo "<option selected></option>";
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
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

</body>
</html>
