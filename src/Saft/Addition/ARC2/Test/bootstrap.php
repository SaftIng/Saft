<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../vendor/autoload.php';

\error_reporting(E_ALL);

global $dbConfig;
if (\file_exists(__DIR__ .'/config.php')) {
    // use custom DB credentials, if available
    $dbConfig = require 'config.php';
} else {
    // standard DB credentials (ready to use in Travis)
    $dbConfig = [
        'db_name' => 'testdb',
        'db_user' => 'root',
        'db_pwd'  => '',
        'db_host' => '127.0.0.1',
    ];
}
