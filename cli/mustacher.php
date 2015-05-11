<?php
error_reporting(E_ALL); //E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 'on');
ini_set('track_errors', 1);

date_default_timezone_set('America/Montreal');

$paths = [
    __DIR__.'/../vendor/autoload.php', // locally
    __DIR__.'/../../autoload.php' // dependency
];
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

use Garden\Cli\Cli;
use Garden\Cli\Schema;
use Mustacher\Mustacher;

$cli = new Cli();

$cli->description('Run mustache templates against a JSON file.')
    ->opt('template:t', 'The path to the template file.', true)
    ->opt('data:d', 'The path to the data json data file.', true)
    ->opt('output:o', 'The path where the output will be written.')
    ->opt('format:f', 'The format of the template file. Either mustache or message.')
    ;

$args = $cli->parse($argv);

try {
    $str = Mustacher::generateFile($args->getOpt('template'), $args->getOpt('data'), $args->getOpt('format', Mustacher::FORMAT_MUSTACHE));
} catch (Exception $ex) {
    echo $cli->red($ex->getMessage()."\n");
    die();
}

if ($args->getOpt('output')) {
    $path = $args->getOpt('output');
    if (!file_exists(dirname($path))) {
        mkdir($path, 0777, true);
    }

    echo "Writing output file: $path\n";
    $r = file_put_contents($path, $str);
    if ($r === false) {
        echo $cli->red("Error writing output file.\n");
    }
} else {
    echo $str;
}
