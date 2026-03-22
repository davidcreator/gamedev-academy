<?php
$pdo=new PDO("mysql:host=localhost","root","");
foreach ($pdo->query("SHOW DATABASES") as $row) { echo $row[0], PHP_EOL; }

