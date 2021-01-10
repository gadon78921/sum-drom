<?php

declare(strict_types=1);

/**
 * Запуск `php sum.php <dir_path_1> ... <dir_path_n>`
 * через пробел передавать пути к директориям
 */

function getDirsFromArgv(array $argv): array
{
    array_shift($argv);

    return $argv;
}

function validateDirs(array $paths): void
{
    foreach ($paths as $path) {
        if (is_dir($path) === false) {
            throw new Exception('Directory path "' . $path .  '" not valid');
        }
    }
}

function iterateDirsAndCalculateSum(array $dirs): float
{
    $totalSum = 0;

    foreach ($dirs as $dir) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        iterator_apply(
            $iterator,
            static function(Iterator $iterator) use (&$totalSum)
            {
                /** @var SplFileInfo $fileInfo */
                $fileInfo = $iterator->current();
                $totalSum += isCountFile($fileInfo) ? getSumFromFile($fileInfo->openFile()) : 0;

                return true;
            },
            [$iterator]
        );
    }

    return $totalSum;
}

function isCountFile(SplFileInfo $fileInfo): bool
{
    return $fileInfo->isFile() && $fileInfo->getFileName() === 'count';
}

function getSumFromFile(SplFileObject $file): float
{
    $sum = 0;
    while (!$file->eof()) {
        $line = $file->fgets();
        preg_match_all('/\-?\d+(\.\d+)?/', $line, $numbers);
        $sum += empty($numbers[0]) ? 0 : array_sum($numbers[0]);
    }

    return $sum;
}

if ($argc === 1) {
    throw new Exception('You must pass directory path(s)');
}

$paths = getDirsFromArgv($argv);
validateDirs($paths);
$totalSum = iterateDirsAndCalculateSum($paths);

echo $totalSum . "\n";
