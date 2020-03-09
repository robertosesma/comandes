<!DOCTYPE html>
<html>
<head>
    <title>Add user</title>
    <meta charset="utf-8">
</head>

<body>
<?php
// Create connection
$conn = new mysqli("localhost", "rsesma", "Amsesr.1977", "comandes");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pswd = password_hash("amsesr2108", PASSWORD_DEFAULT);

// insert new uf
// $sql = "INSERT INTO users (user_id, username, password) VALUES (1, 'rsesma', '".$pswd."')";

// update existing uf
$sql = "UPDATE uf SET psswd = '".$pswd."' WHERE uf=3";

if ($conn->query($sql)) {
  echo "Query executed.";
} else{
  echo "Query error.";
}
?>

</body>
</html>
