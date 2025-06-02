<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendari</title>
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

    $edit = $_SESSION["admin"] || $_SESSION["calendar"];
    
    // get calendari
    $stmt = $conn -> prepare("SELECT c.fecha, 
        c.uc1, uf1.descrip AS desc1, c.uc2, uf2.descrip AS desc2,
        c.cerrado, c.asamblea, c.coment
        FROM comandes.calendari c
        LEFT JOIN comandes.uf uf1 on (uf1.uf = c.uc1)
        LEFT JOIN comandes.uf uf2 on (uf2.uf = c.uc2)
        WHERE fecha >= '2024-01-01' ORDER BY fecha");
    // $stmt->bind_param('s', date('Y-m-d'));
    $stmt->execute();
    $cal = $stmt->get_result();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h1>Calendari d'Obertures</h1>
        <p><a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a></p>
    </div>

    <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
        <tbody>
        <?php while ($r = mysqli_fetch_array($cal)) { 
            $fecha = $r['fecha'];
            if ($edit) $fecha = "<a href='".'edit_calendari.php?&fecha='.$fecha."'>".$fecha."</a>";

            $u1 = ($r['cerrado'] ? 'TANCAT' : $r['desc1']);
            if ($r['uc1']==$uf) $u1 = '<mark><strong>'.$u1.'</strong></mark>';

            $u2 = ($r['cerrado'] ? 'TANCAT' : $r['desc2']);
            if ($r['uc2']==$uf) $u2 = '<mark><strong>'.$u2.'</strong></mark>';
            
            $t = ($r['cerrado'] ? 'table-danger' : ($r['asamblea'] ? 'table-primary' : 'table-default'));
            $c = ($r['asamblea'] ? "ASSAMBLEA " : "");
            $c = $c.$r['coment'];
        ?>
            <tr class= <?php echo $t; ?>>
                <td><?php echo $fecha; ?></td>
                <td><?php echo $u1; ?></td>
                <td><?php echo $u2; ?></td>
                <td><?php echo $c; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php $conn->close();

} else {
    header("Location: logout.php");
}?>

</body>
</html>
