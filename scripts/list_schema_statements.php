<?php
$schema = __DIR__ . '/../install/database/schema.sql';
$handle = fopen($schema, 'r');
$buffer = '';
$num = 0;
while (($line = fgets($handle)) !== false) {
    $trim = trim($line);
    if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
        continue;
    }
    $buffer .= $line;
    if (str_ends_with(rtrim($line), ';')) {
        $num++;
        $stmt = trim($buffer);
        $preview = substr(preg_replace('/\s+/', ' ', $stmt), 0, 120);
        printf("[%d] %s\n", $num, $preview);
        $buffer = '';
    }
}
fclose($handle);
