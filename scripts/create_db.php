<?php
$pdo=new PDO("mysql:host=localhost","root","");
$db="gda_test";
$pdo->exec("DROP DATABASE IF EXISTS `$db`");
$pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "created $db\n";

