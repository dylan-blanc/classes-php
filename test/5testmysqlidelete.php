<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../user.php';

$u = new User(1, '', '', '', '',);
var_dump($u->update($conn, 'loginchanger', 'TestMDPdifferent', 'lololo@gmail.com', 'Bonsoir', 'Jean'));



var_dump($u->delete($conn));
$s = $conn->prepare("SELECT COUNT(*) c FROM utilisateurs WHERE id = ?");
$id = 1;
$s->bind_param("i", $id);
$s->execute();
$s->bind_result($c);
$s->fetch();
$s->close();
var_dump($c);
