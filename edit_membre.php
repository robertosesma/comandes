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
$mobErr = "";
$err = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])) {
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $add = clear_input($_POST["add"]);
        $uf = clear_input($_POST["uf"]);
        $n = clear_input($_POST["n"]);

        $nom = clear_input($_POST["nom"]);
        $ape = clear_input($_POST["ape"]);
        $mail = clear_input($_POST["mail"]);

        // verificar mòbil
        $mobil = clear_input($_POST["mobil"]);
        if (strlen($mobil)!=9 || substr($mobil,0,1)!="6") {
            $mobErr = "El telèfon mòbil ha de tenir 9 caràcters i ha de començar per 6";
            $err = true;
        }
        if (!$err) {
            if ($add==1) {
                // afegir nou membre
                $n = getnextmembre($conn,$uf);
                $stmt = $conn -> prepare("INSERT INTO membres (uf,n,nom,ape,tel,email) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('iissss', $uf, $n, $nom, $ape, $mobil, $mail);
            } else {
                // editar membre existent
                $stmt = $conn -> prepare("UPDATE membres SET nom=?, ape=?, tel =?, email=? WHERE uf=? AND n=?");
                $stmt->bind_param('ssssii', $nom, $ape, $mobil, $mail, $uf, $n);
            }
            $stmt->execute();
            header("Location: edit_uc.php?add=0");
        }
    } else {
        $add = clear_input($_GET["add"]);
        $uf = $_SESSION['username'];
        $n = clear_input($_GET["n"]);
        if ($add==0) {
            // obtenir les dades d'un membre
            $stmt = $conn -> prepare("SELECT * FROM membres WHERE uf=? AND n=?");
            $stmt->bind_param('ii', $uf, $n);
            $stmt->execute();
            $res = $stmt->get_result();
            $d = $res->fetch_assoc();
            $nom = $d["nom"];
            $ape = $d["ape"];
            $mobil = $d["tel"];
            $mail = $d["mail"];
            $res->free();
        } else {
            // afegir un nou membre
            $nom = '';
            $ape = '';
            $mobil = '';
            $mail = '';
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h2><?php echo ($add==1 ? "Afegir" : "Editar"); ?> membre</h2>
        <a class="btn btn-link" href="edit_uc.php?add=0">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" name="nom" required value="<?php echo $nom;?>">
        </div>
        <div class="form-group">
            <label for="ape">Cognoms:</label>
            <input type="text" class="form-control" name="ape" required value="<?php echo $ape;?>">
        </div>
        <div class="form-group">
            <label for="mobil">Mòbil:</label>
            <input type="tel" class="form-control" name="mobil" placeholder="612612612"
            pattern="[0-9]{9}" required value="<?php echo $mobil;?>">
        </div>
        <div class="form-group">
            <span class="error text-danger"><?php echo $mobErr;?></span>
        </div>
        <div class="form-group">
            <label for="mail">Correu electrònic:</label>
            <input type="email" class="form-control" name="mail" value="<?php echo $mail; ?>">
        </div>

        <p> De conformitat amb la Llei Orgànica de Protecció de Dades de Caràcter Personal 15/1999, li comuniquem
        que les seves dades s'incorporaran a la base de dades de caràcter personal de la que és titular la Cooperativa
        de Consum i Resistència de Terrassa, la finalitat de la qual és el correcte funcionament del sistema de
        comandes. Així mateix, li informem que les seves dades no seran cedides a tercers sense el seu consentiment.
        Pot exercir els seus drets d'Accés, Rectificació, Cancel·lació i Oposició a
        <a href="https://atzur.org/">https://atzur.org/</a>.</p>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="accept" required>
            <label class="form-check-label">Accepto cedir les meves dades</label>
        </div>

        <input type="text" class="form-control" hidden="true" name="uf" value=" <?php echo $uf; ?> ">
        <input type="text" class="form-control" hidden="true" name="n" value=" <?php echo $n; ?> ">
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
