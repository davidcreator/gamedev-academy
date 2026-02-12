<?php
$file = '../../includes/functions.php';
echo "<h3>Conteúdo de functions.php:</h3>";
echo "<pre>";
echo htmlspecialchars(file_get_contents($file));
echo "</pre>";