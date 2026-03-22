<?php
$start=microtime(true);
$pdo=new PDO("mysql:host=localhost;dbname=gda_test;charset=utf8mb4","root","");
echo "connected\n";
echo "time=".(microtime(true)-$start)."s\n";

