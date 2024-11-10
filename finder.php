<?php



$directory = __DIR__ . '/datafiles';

if (!is_dir($directory)) {
    die("Папка '$directory' не найдена.\n");
}

/**
 * Получаем список всех файлов в папке.
 * @var array $files
 */
$files = scandir($directory);

/**
 * Массив для хранения имен файлов, соответствующих критериям.
 * @var array $matchingFiles
 */
$matchingFiles = [];


$pattern = '/^[a-zA-Z0-9]+\.ixt$/';


foreach ($files as $file) {
    if (is_file($directory . '/' . $file) && preg_match($pattern, $file)) {
        $matchingFiles[] = $file;
    }
}

sort($matchingFiles);


if (count($matchingFiles) > 0) {
    echo "Найденные файлы:\n";
    foreach ($matchingFiles as $file) {
        echo $file . "\n";
    }
} else {
    echo "Файлы, соответствующие критериям, не найдены.\n";
}

