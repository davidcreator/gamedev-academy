<?php
// logout.php

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->logout();

redirect(url());