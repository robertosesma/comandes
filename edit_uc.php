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
$pswdErr = "";
$descripErr = "";
$err = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();
    $add = clear_input($_GET["add"]);
    $admin = $_SESSION['admin'];

    if ($add==1 && $admin!=1) {
        $ok = false;        // només els administradors poden afegir noves UC
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $add = clear_input($_POST["add"]);
            $uf = clear_input($_POST["uf"]);
            $descrip = clear_input($_POST["descrip"]);
            $mail = clear_input($_POST["mail"]);
            if ($admin==1) {
                $activado = clear_input($_POST["act"]=="activado");
                $activado = ($activado == 1 ? 1 : 0);
                $admin = clear_input($_POST["admin"]=="activado");
                $admin = ($admin == 1 ? 1 : 0);
                $isadmin = $admin;
            } else {
                $activado = 1;
                $admin = 0;
            }
            $pswd = clear_input($_POST["pswd1"]);
            // verificar contrasenya
            $password = '';
            if (strlen($pswd)>=8) {
                $password = password_hash($pswd, PASSWORD_DEFAULT);
            } else {
                if (strlen($pswd)>0 || $add==1) {
                    $pswdErr = "La contrasenya ha de tenir com a mínim 8 caràcters";
                    $err = true;
                }
            }
            $stmt = $conn -> prepare("SELECT * FROM uf WHERE descrip=?");
            $stmt->bind_param('s', $descrip);
            $stmt->execute();
            $dades = $stmt->get_result();
            $nrows = $dades->num_rows;
            if ($nrows > 0) {
                $descripErr = "El nom de la unitat de convivència ja existeix";
                $err = true;
            }
            if (!$err) {
                //  no hi ha errors
                if ($add==1) {
                    // afegir nova UC
                    $uf = getnextuf($conn);
                    $stmt = $conn -> prepare("INSERT INTO uf (uf,descrip,psswd,email,act,admin) VALUES (?,?,?,?,?,?)");
                    $stmt->bind_param('isssii', $uf, $descrip, $password, $mail, $activado, $admin);
                } else {
                    // editar UC existent
                    if (strlen($pswd)>0) {
                        $stmt = $conn -> prepare("UPDATE uf SET descrip=?, email=?, psswd =?, act=?, admin=? WHERE uf=?");
                        $stmt->bind_param('sssiii', $descrip, $mail, $password, $activado, $admin, $uf);
                    } else {
                        $stmt = $conn -> prepare("UPDATE uf SET descrip=?, email=?, act=?, admin=? WHERE uf=?");
                        $stmt->bind_param('ssiii', $descrip, $mail, $activado, $admin, $uf);
                    }
                }
                $stmt->execute();
                if ($admin==1) {
                    header("Location: admin_uc.php");
                } else {
                    header("Location: init.php");
                }
            }
        } else {
            if ($add==0) {
                if ($admin==1 && isset($_GET['uc'])) {
                    $uf = clear_input($_GET["uc"]);
                } else {
                    $uf = $_SESSION['username'];
                }
                // editar una UC que ja existeix: obtenir les dades
                $stmt = $conn -> prepare("SELECT * FROM uf WHERE uf=?");
                $stmt->bind_param('i', $uf);
                $stmt->execute();
                $dades = $stmt->get_result();
                $nrows = $dades->num_rows;
                if ($nrows > 0) {
                    while($r = $dades->fetch_assoc()) {
                        $descrip = $r["descrip"];
                        $mail = $r["email"];
                        $activado = $r["act"];
                        $isadmin = $r["admin"];
                    }
                } else {
                    $ok = false;
                }
            } else {
                // afegir una UC nova
                $uf = '';
                $descrip = '';
                $mail = '';
                $activado = 1;              // activada per defecte
                $pswd = generatepswd(8);    // generate pswd: by default 8 chars
            }
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2><?php echo ($add==1 ? "Nova" : "Editar dades"); ?> UC</h2>
        <a class="btn btn-link" href=<?php echo ($admin==1 ? "admin_uc.php" : "init.php"); ?>>Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
        <?php if ($add==0) { ?>
            oninput='pswd2.setCustomValidity(pswd2.value != pswd1.value ? "Les contrasenyes no coincideixen." : "")' <?php } ?>>
        <div class="form-group">
            <label for="descrip">Nom unitat de convivència:</label>
            <input type="text" class="form-control" name="descrip" required value="<?php echo $descrip;?>">
        </div>
        <div class="form-group">
            <span class="error text-danger"><?php echo $descripErr;?></span>
        </div>
        <div class="form-group">
            <label for="mail">Correu electrònic:</label>
            <input type="email" class="form-control" name="mail" required value="<?php echo $mail; ?>">
        </div>
        <div class="form-group">
            <label for="pswd1">Contrasenya (mínim 8 caràcters):</label>
            <?php if ($add==1) { ?>
            <input type="text" class="form-control" name="pswd1" required value="<?php echo $pswd; ?>">
            <?php } else { ?>
            <input type="password" class="form-control" name="pswd1">
            <?php } ?>
        </div>
        <div class="form-group">
            <span class="error text-danger"><?php echo $pswdErr;?></span>
        </div>
        <?php if ($add==0) { ?>
        <div class="form-group">
            <label for="pswd2">Confirma la contrasenya:</label>
            <input type="password" class="form-control" name="pswd2">
        </div>
        <?php } ?>
        <?php if ($admin==1) { ?>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <?php $check_act = ($activado==1 ? "checked" : ""); ?>
                    <input type="checkbox" class="custom-control-input" name="act" id="act"
                        value="activado" <?php echo $check_act; ?>>
                    <label class="custom-control-label" for="act">Activa</label>
                </div>
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <?php $check_admin = ($isadmin==1 ? "checked" : ""); ?>
                    <input type="checkbox" class="custom-control-input" name="admin" id="admin"
                        value="activado" <?php echo $check_admin; ?>>
                    <label class="custom-control-label" for="admin">Administrador</label>
                </div>
            </div>
        <?php } ?>
        <input type="text" class="form-control" hidden="true" name="uf" value=" <?php echo $uf; ?> ">
        <input type="text" class="form-control" hidden="true" name="add" value=" <?php echo $add; ?> ">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
