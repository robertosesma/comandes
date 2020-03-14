<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Dades UC</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
$error = false;
$pswdErr = "";
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $uf = $_SESSION['username'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $descrip = clear_input($_POST["uc"]);
        $mail = clear_input($_POST["mail"]);
        $pswdChange = false;
        $pswd = clear_input($_POST["pswd1"]);
        if (strlen($pswd)>=8) {
            $pswdChange = true;
            $password = password_hash($pswd, PASSWORD_DEFAULT);
        } else {
            if (strlen($pswd)>0) {
                $error = true;
                $pswdErr = "La contrasenya ha de tenir com a mínim 8 caràcters";
            }
        }

        if (!$error) {
            if ($pswdChange) {
                $stmt = $conn -> prepare("UPDATE uf SET descrip=?, email=?, psswd =? WHERE uf=?");
                $stmt->bind_param('sssi', $descrip, $mail, $password, $uf);
            } else {
                $stmt = $conn -> prepare("UPDATE uf SET descrip=?, email=? WHERE uf=?");
                $stmt->bind_param('ssi', $descrip, $mail, $uf);
            }
            $stmt->execute();
            echo '<script language="javascript">alert("Dades actualitzades")</script>';
        }
    }

    $stmt = $conn -> prepare('SELECT * FROM uf WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $user = $stmt->get_result();
    if ($user->num_rows > 0) {
        while($r = $user->fetch_assoc()) {
            $descrip = $r["descrip"];
            $mail = $r["email"];
        }
    }
} else {
    $ok = false;
    header("Location: index.php");
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2>Dades UC: <?php echo $descrip; ?></h2>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
        oninput='pswd2.setCustomValidity(pswd2.value != pswd1.value ? "Les contrasenyes no coincideixen." : "")'>
        <div class="form-group">
            <label for="uc">Nom unitat de convivència:</label>
            <input type="text" class="form-control" name="uc" id="uc" required value=" <?php echo $descrip; ?> ">
        </div>
        <div class="form-group">
            <label for="mail">Correu electrònic:</label>
            <input type="email" class="form-control" name="mail" id="mail" required  value=" <?php echo $mail; ?> ">
        </div>
        <div class="form-group">
            <label for="pswd1">Contrasenya (mínim 8 caràcters):</label>
            <input type="password" class="form-control" name="pswd1" id="up">
        </div>
        <div class="form-group">
            <span class="error text-danger"><?php echo $pswdErr;?></span>
        </div>
        <div class="form-group">
            <label for="pswd2">Confirma la contrasenya:</label>
            <input type="password" class="form-control" name="pswd2" id="up2">
        </div>
        <input type="text" class="form-control" hidden="true" name="uf" value=" <?php echo $uf; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php
    $conn->close();
} else {
    header("Location: index.php");
}?>


</body>
</html>
