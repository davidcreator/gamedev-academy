<?php
ini_set("session.save_path", __DIR__ . "/../tmp/sessions");
session_start();
$config = [
  "host" => "localhost",
  "name" => "gda_test",
  "user" => "root",
  "pass" => "",
  "port" => 3306
];
$schemaPath = __DIR__ . '/../install/database/schema.sql';
$pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['name'] . ';charset=utf8mb4', $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$sql = file_get_contents($schemaPath);
$statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)), fn($s)=>$s!=='' && strpos($s,'--')!==0);
$pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
$pdo->exec('SET UNIQUE_CHECKS=0;');
$i=0;
foreach($statements as $stmt){
  $i++;
  $preview = substr(preg_replace('/\s+/', ' ', $stmt),0,90);
  echo "[$i] $preview\n";
  try{
    $pdo->exec($stmt);
  }catch(Exception $e){
    echo "ERROR at $i: " . $e->getMessage() . "\n";
    break;
  }
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
$pdo->exec('SET UNIQUE_CHECKS=1;');
