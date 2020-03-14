<?php
function connect(){
    $config = parse_ini_file('db.ini');
    $con = mysqli_connect("localhost",$config['username'],$config['password'],$config['db']);
    if(!$con){
        die("Failed to connect to Database");
    }
    $con->query("SET NAMES 'utf8'");
    $con->query("SET CHARACTER SET utf8");
    $con->query("SET SESSION collation_connection = 'utf8_unicode_ci'");

    return $con;
}

function clear_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getdescrip($conn,$uf){
    $stmt = $conn -> prepare('SELECT descrip FROM uf WHERE uf = ?');
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $users = $stmt->get_result();
    $descrip = "";
    if ($users->num_rows > 0) {
        while($r = $users->fetch_assoc()) {
            $descrip = $r["descrip"];
        }
    }
    return $descrip;
}

function gettotal($conn,$uf,$fecha){
    $stmt = $conn -> prepare("SELECT Sum(total) AS total FROM comanda WHERE uf = ? AND fecha = ? GROUP BY fecha, uf");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $totals = $stmt->get_result();
    if ($totals->num_rows > 0) {
        while($r = $totals->fetch_assoc()) {
            $uctotal = ($r["total"]==NULL ? '' : number_format($r["total"], 2, ",", ".")."€");
        }
    }
    return $uctotal;
}

function isopen(){
    $day = date("N");
    $hour = date("H");

    $open = false;
    $config = parse_ini_file('open.ini');
    if (($day >= $config['dini']) && ($day <= $config['dend'])) {
        $open = true;
        if ((($day == $config['dini']) && ($hour < $config['hini'])) ||
            (($day == $config['dend']) && ($hour > $config['hend']))) {
            $open = false;
        }
    }
    return $open;
}

function getnext(){
    $config = parse_ini_file('open.ini');
    $fecha = date('Y-m-d',strtotime($config['next']));
    return $fecha;
}

?>
