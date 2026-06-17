<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Quotes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username']) &&
        ($_SESSION['tresorer']==1 || $_SESSION['admin']==1)) {
    $conn = connect();
    // anys disponibles
    $yini = getyini();
    $yend = getyend();
    for ($y = $yini; $y <= $yend; $y++) {
        $years = $years.'<a class="btn btn-link" href="quotes.php?year='.$y.'">'.$y.'</a>';
    }
    // es demanen dades d'un any
    $year = 0;
    if (isset($_GET['year'])) {
        $year = clear_input($_GET["year"]);
        // es demanen quotes pendents?
        $pendent = 0;
        if (isset($_GET['pendent'])) {
            $pendent = clear_input($_GET["pendent"]);
        }
    }
    // obtenir dades uf i quotes any
    if ($year>0) {
        $stmt = $conn -> prepare("SELECT uf, descrip, act FROM uf WHERE uf < 10000 ORDER BY act DESC, descrip");
        $stmt->execute();
        $ufs = $stmt->get_result();
        if ($ufs->num_rows==0) {
            $ok = false;
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="jumbotron">
        <h1>Quotes <?php echo ($year!=0 ? $year : "") ?></h1>
        <?php if ($year!=0) {
            $stmt = $conn -> prepare("SELECT SUM(quota) total FROM quotes WHERE year = ?");
            $stmt->bind_param('i', $year);
            $stmt->execute();
            $res = $stmt->get_result();
            $t = mysqli_fetch_array($res);
            echo "<h3>TOTAL ".getascurr($t["total"],"€")."</h3>"; }?>
        Any <?php echo $years?><br>
        <?php if ($year!=0) {
            echo "<a class='btn btn-link' href='quotes.php?year=".$year."&pendent=1'>Pendents</a>";
            echo "<a class='btn btn-link' href='quotes.php?year=".$year."'>Totes</a>";
        } ?>
        <a class="btn btn-link" href="init.php">Tornar</a>
        <a class="btn btn-link" href="logout.php">Sortir</a>
    </div>
</div>

<div class="container">
    <?php if ($year!=0) { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">UC</th>
                    <th scope="col"><div class="text-center"><?php echo $year; ?></div></th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 0;
                while ($r = mysqli_fetch_array($ufs)) {
                    $n = $n+1;
                    $uf = $r["uf"];
                    $stmt = $conn -> prepare("SELECT quota FROM quotes WHERE uf=? AND year=?");
                    $stmt->bind_param('ii', $uf, $year);
                    $stmt->execute();
                    $qs = $stmt->get_result();
                    $ispendent = $qs->num_rows==0;
                    if ($ispendent) {
                        $s = '<a class="btn btn-link" href="edit_quota.php?uf='.$uf.'&y='.$year.'&p='.$pendent.'">Pendent</a>';
                    } else {
                        $q = mysqli_fetch_array($qs);
                        $s = '<a class="btn btn-link" href="edit_quota.php?uf='.$uf.'&y='.$year.'&p='.$pendent.'">Pagat ('.$q["quota"].'€)</a>';
                    }
                    $qs->free();
                    if ($pendent==0 || ($pendent==1 && $ispendent)) {
                        echo ($r["act"]==1 ? "<tr>" : "<tr class=table-secondary>"); ?>
                            <th scope="row"><?php echo $n; ?></th>
                            <td><?php echo $r["descrip"]; ?></td>
                            <td><div class="text-center"><?php echo $s; ?></div></td>
                        </tr>
                    <?php }
                }
                $ufs->free(); ?>
            </tbody>
        </table>
<?php } ?>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
