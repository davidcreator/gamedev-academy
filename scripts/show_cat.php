<?php
$lines = file('install/database/schema.sql');
foreach ($lines as $i => $line) {
    if (strpos($line, 'CREATE TABLE `categories`') !== false) {
        for ($j = $i - 3; $j <= $i + 40; $j++) {
            if (!isset($lines[$j])) continue;
            echo ($j + 1) . ' ' . $lines[$j];
        }
        break;
    }
}
