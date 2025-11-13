<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'classes';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Erreur de connexion (' . $conn->connect_errno . ') ' . $conn->connect_error);
}

require_once 'src/user.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blabla</title>
</head>

<body>
    <h1>TEST</h1>


</body>

</html>
    