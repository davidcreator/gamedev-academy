<?php
// teste-news.php
$files = glob('news*.php');
echo "<h3>Arquivos de Notícias encontrados:</h3>";
echo "<pre>";
print_r($files);
echo "</pre>";
?>