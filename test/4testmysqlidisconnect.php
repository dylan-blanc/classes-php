<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../user.php';

$u = new User(1, '', '', '', '');
var_dump($u->update($conn, 'loginchanger', 'TestMDPdifferent', 'lololo@gmail.com', 'Bonsoir', 'Jean'));

echo "connect: ";
var_dump($u->connect('loginchanger', 'TestMDPdifferent'));

echo "isConnected: ";
var_dump($u->isConnected());

echo "_SESSION: ";
var_dump($_SESSION);

echo "getAllinfos : ";
var_dump($u->getAllinfos());

echo "disconnect: ";
$u->disconnect();
var_dump($u->isConnected());
