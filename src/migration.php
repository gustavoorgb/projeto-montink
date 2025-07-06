<?php
date_default_timezone_set('America/Fortaleza');

use App\Config\Migration\CLI;
use App\Config\Env;

require_once(dirname(__DIR__, 1) . '/vendor/autoload.php');

Env::load(__DIR__ . '/../.env');

$cli = new CLI();
$cli->run($argv);
