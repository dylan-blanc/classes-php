<?php
require __DIR__ . '/../user.php';
$u = new User(1, '', '', '', '');
var_dump($u->update($conn, 'loginchanger', 'TestMDPdifferent', 'lololo@gmail.com', 'Bonsoir', 'Jean'));
var_dump($u->getAllinfos());
