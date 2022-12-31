<?php
function connect(){
    require '../dbconfig.php';

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
        $r = $users->fetch_assoc();
        $descrip = $r["descrip"];
    }
    return $descrip;
}

function gettotal($conn,$uf,$fecha){
    $stmt = $conn -> prepare("SELECT SUM(total) AS total FROM comanda WHERE uf = ? AND fecha = ?");
    $stmt->bind_param('is', $uf, $fecha);
    $stmt->execute();
    $totals = $stmt->get_result();
    $uctotal = '';
    if ($totals->num_rows > 0) {
        $r = $totals->fetch_assoc();
        $uctotal = ($r["total"]==NULL ? '' : number_format($r["total"], 2, ",", ".")."€");
    }
    return $uctotal;
}

function getsubtotal($conn,$uf,$fecha,$grupo){
    $stmt = $conn -> prepare("SELECT SUM(total) AS subtotal FROM comanda WHERE fecha =? AND uf=? AND cgrupo=?");
    $stmt->bind_param('sii', $fecha, $uf, $grupo);
    $stmt->execute();
    $totals = $stmt->get_result();
    $subtotal ='';
    if ($totals->num_rows > 0) {
        $r = $totals->fetch_assoc();
        $subtotal = ($r["subtotal"]==NULL ? '' : $r["subtotal"]);
    }
    return $subtotal;
}

function ishorari_act($conn){
    // sistema horari activat
    $stmt = $conn -> prepare("SELECT * FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    $r = mysqli_fetch_array($dades);
    $horari_act = ($r["horari_act"]==1);
    $dades->free();
    return $horari_act;
}

function isopen($conn){
    // carregar dia/hora inici, dia/hora fin
    $stmt = $conn -> prepare("SELECT * FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    $r = mysqli_fetch_array($dades);
    $dini = $r["dini"];
    $hini = $r["hini"];
    $dend = $r["dend"];
    $hend = $r["hend"];
    // dia i hora actuals
    $day = date("N");
    $hour = date("H");
    // comprovar si estem en temps de comanda
    $open = false;
    if (($day >= $dini) && ($day <= $dend)) {
        $open = true;
        if ((($day == $dini) && ($hour < $hini)) ||
            (($day == $dend) && ($hour > $hend))) {
            $open = false;
        }
    }
    return $open;
}

function getnext($conn){
    $stmt = $conn -> prepare("SELECT next FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    $r = mysqli_fetch_array($dades);
    $next = date('Y-m-d',strtotime($r["next"]));
    return $next;
}

function getmax($conn){
    $stmt = $conn -> prepare("SELECT max_uf FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    $r = mysqli_fetch_array($dades);
    $max = $r["max_uf"];
    return $max;
}

function getnextitem($conn){
    $stmt = $conn -> prepare("SELECT MAX(tipo) AS max FROM dtipo;");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnextuf($conn){
    $stmt = $conn -> prepare("SELECT MAX(uf) AS max FROM uf WHERE uf < 10000;");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnextmembre($conn,$uf){
    $stmt = $conn -> prepare("SELECT MAX(n) AS max FROM membres WHERE uf = ?;");
    $stmt->bind_param('i', $uf);
    $stmt->execute();
    $max = $stmt->get_result();
    $nrows = $max->num_rows;
    if ($nrows>0) {
        $r = $max->fetch_assoc();
        $next = $r["max"]+1;
    } else {
        $next = 1;
    }
    return $next;
}

function getnextprod($conn){
    $stmt = $conn -> prepare("SELECT MAX(cod) AS max FROM dgrupo;");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function gethhmm($conn,$id){
    $stmt = $conn -> prepare("SELECT * FROM admin");
    $stmt->execute();
    $dades = $stmt->get_result();
    $r = mysqli_fetch_array($dades);

    $hini = $r["hora"];
    $d = $r["delta"];

    $n = (60/$d);
    $f = floor($id/($n+1));
    $hora = $hini + $f;
    $min = ($id - $n*$f - 1) * $d;
    $t = str_pad($hora,2,"0",STR_PAD_LEFT).":".str_pad($min,2,"0",STR_PAD_LEFT);

    return $t;
}

function getyini(){
    return 2022;
}

function getyend(){
    // date("Y");
    return 2023;
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

function getmes($m){
    $mes = "";
    if ($m==1) $mes = "Gen";
    if ($m==2) $mes = "Feb";
    if ($m==3) $mes = "Mar";
    if ($m==4) $mes = "Abr";
    if ($m==5) $mes = "Mai";
    if ($m==6) $mes = "Jun";
    if ($m==7) $mes = "Jul";
    if ($m==8) $mes = "Ago";
    if ($m==9) $mes = "Set";
    if ($m==10) $mes = "Oct";
    if ($m==11) $mes = "Nov";
    if ($m==12) $mes = "Des";
    return $mes;
}

?>
