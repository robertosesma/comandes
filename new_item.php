<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Afegir producte</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && ((isset($_GET['uf']) && isset($_GET['fecha'])) || $_SERVER["REQUEST_METHOD"] == "POST")) {
    $uf = $_GET["uf"];
    $fecha = $_GET["fecha"];

    echo "UF = ".$uf." fecha = ".$fecha;

} else {
    $ok = false;
    header("Location: index.php");
}
?>

</body>
</html>
