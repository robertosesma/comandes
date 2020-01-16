<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>cdteca Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<div class="container">
    <div class="jumbotron">
        <h1>comandes</h1>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="username">Unitat de convivència:</label>
            <input type="text" class="form-control" name="username">
            <span class="error text-danger"><?php echo $usernameErr;?></span>
        </div>
        <div class="form-group">
            <label for="pswd">Contrasenya:</label>
            <input type="password" class="form-control" name="pswd">
            <span class="error text-danger"><?php echo $pswdErr;?></span>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

</body>
</html>
