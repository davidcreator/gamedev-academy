<?php
ini_set('session.save_path', __DIR__ . '/../tmp/sessions');
session_start();
$_SESSION['db_config'] = [
    'host' => 'localhost',
    'name' => 'gda_test',
    'user' => 'root',
    'pass' => '',
    'port' => 3306
];
include __DIR__ . '/../install/assets/js/ajax/create_tables_ajax_mysqli.php';
