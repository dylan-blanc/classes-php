<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'nom_de_la_base';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Erreur de connexion (' . $conn->connect_errno . ') ' . $conn->connect_error);
}




?>