<?php
require __DIR__ . '/../user.php';
$u = new User(null, '', '', '', '');
var_dump($u->register($conn, 'Testlogin', 'TestMDP', 'lalala@gmail.com', 'Bonjour', 'Jean'));
var_dump($u->getAllinfos());
