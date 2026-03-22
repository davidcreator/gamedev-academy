<?php
$start=microtime(true);
$pdo=new PDO("mysql:host=localhost","root","");
$elapsed = microtime(true)-$start;
echo "connect: {$elapsed}s\n";

