<?php

use engine\DB;

$config = include('../config.php');
[$driver, $host, $username, $password, $dbname] = $config;
$pdo = (new DB($driver, $host, $username, $password, $dbname))->connect();
$main_dir = dirname(__DIR__);

$dir = $main_dir . '/migrations/';
$allFiles = glob($dir . '*.sql');

$query = $pdo->query("SHOW TABLES LIKE 'versions';");
$data = $query->fetchAll();

if (!count($data)) {
    return $allFiles;
}
$versionsFiles = array();
$data = $pdo->query('SELECT `name` FROM `versions`;');
foreach ($data as $row) {
    $versionsFiles[] = $dir . $row['name'];
}

$files = array_diff($allFiles, $versionsFiles);

if (empty($files)) {
    echo 'База в актуальном состоянии' . PHP_EOL;
} else {
    foreach ($files as $file) {
        $command = file_get_contents($file);
        $pdo->exec($command);

        $baseName = basename($file);
        $query = $pdo->prepare('INSERT INTO `versions` (`name`) VALUES(:basename)');
        $query->bindParam(':basename', $baseName);
        $query->execute();
    }
    echo 'Миграции успешно применены' . PHP_EOL;
}
