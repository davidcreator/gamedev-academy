<?php
$pdo=new PDO("mysql:host=localhost;dbname=gamedev_academy","root","");
foreach ($pdo->query("SHOW TABLES") as $row) { echo $row[0], PHP_EOL; }

