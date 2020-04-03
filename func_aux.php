<?php
$max_users = 10000;

function connect(){
    require 'dbconfig.php';

    $con = mysqli_connect($dbconfig['server'],$dbconfig['username'],$dbconfig['password'],$dbconfig['db']);
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

function getsubtotal($conn,$uf,$fecha,$grupo,$format){
    $stmt = $conn -> prepare("SELECT SUM(total) AS subtotal FROM comanda WHERE fecha =? AND uf=? AND cgrupo=?");
    $stmt->bind_param('sii', $fecha, $uf, $grupo);
    $stmt->execute();
    $totals = $stmt->get_result();
    if ($totals->num_rows > 0) {
        while($r = $totals->fetch_assoc()) {
            if ($format) {
                $subtotal = ($r["subtotal"]==NULL ? '' : number_format($r["subtotal"], 2, ",", ".")."€");
            } else {
                $subtotal = ($r["subtotal"]==NULL ? '' : $r["subtotal"]);
            }

        }
    }
    return $subtotal;
}

function isopen(){
    require 'dbconfig.php';

    $day = date("N");
    $hour = date("H");

    $open = false;
    $next=getnext();
    $dini=$horari["dini"];
    $hini=$horari["hini"];
    $dend=$horari["dend"];
    $hend=$horari["hend"];
    if (($day >= $dini) && ($day <= $dend)) {
        $open = true;
        if ((($day == $dini) && ($hour < $hini)) ||
            (($day == $dend) && ($hour > $hend))) {
            $open = false;
        }
    }
    return $open;
}

function getnext(){
    require 'dbconfig.php';
    return $next = date('Y-m-d',$horari["next"]);
}

function getnextitem($conn){
    $next = '';
    $stmt = $conn -> prepare("SELECT MAX(tipo) AS max FROM dtipo;");
    $stmt->execute();
    $max = $stmt->get_result();
    if ($max->num_rows > 0) {
        while($r = $max->fetch_assoc()) {
            $next = $r["max"]+1;
        }
    }
    return $next;
}

function getnextuf($conn){
    $next = '';
    $stmt = $conn -> prepare("SELECT MAX(uf) AS max FROM uf;");
    $stmt->execute();
    $max = $stmt->get_result();
    if ($max->num_rows > 0) {
        while($r = $max->fetch_assoc()) {
            $next = $r["max"]+1;
        }
    }
    return $next;
}

function getnextprod($conn){
    $next = '';
    $stmt = $conn -> prepare("SELECT MAX(cod) AS max FROM dgrupo;");
    $stmt->execute();
    $max = $stmt->get_result();
    if ($max->num_rows > 0) {
        while($r = $max->fetch_assoc()) {
            $next = $r["max"]+1;
        }
    }
    return $next;
}

function generatepswd($length){
    // create new pasword
    // symbols to use
    $symbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890._';
    $symbols_length = strlen($symbols) - 1;        //strlen starts from 0 so to get number of characters deduct 1
    $pswd = '';
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $symbols_length);      // get a random character from the string with all characters
        $pswd .= $symbols[$n];              // add the character to the password string
    }
    return $pswd;
}

function getascurr($v,$currency){
    return ($v==NULL ? '' : number_format($v, 2, ",", ".").$currency);
}

?>
