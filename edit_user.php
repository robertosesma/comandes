<?php
include 'func_aux.php';
$conn = connect();
$stmt = $conn -> prepare("SELECT * FROM uf");
$stmt->execute();
$uf = $stmt->get_result();
while ($r = mysqli_fetch_array($uf)) {
    if ($r["psswd"]==NULL) {
        // symbols to use
        $symbols = 'abcdefghijklmnopqrstuvwxyz';
        $symbols .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols .= '1234567890';
        $symbols .= '._';
        $symbols_length = strlen($symbols) - 1;        //strlen starts from 0 so to get number of characters deduct 1

        $pass = '';
        $length = 8;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $symbols_length);      // get a random character from the string with all characters
            $pass .= $symbols[$n];              // add the character to the password string
        }

        $pswd = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn -> prepare("UPDATE uf SET psswd =? WHERE uf=?");
        $stmt->bind_param('si',$pswd,$r["uf"]);
        $stmt->execute();

        echo "uf = ".$r["uf"]."    pswd = ".$pass."<br>";
    }
}
?>
