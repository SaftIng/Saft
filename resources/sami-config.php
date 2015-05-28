<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$root = realpath(__DIR__) . DIRECTORY_SEPARATOR . '..';
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($root . DIRECTORY_SEPARATOR .'src')
;

return new Sami($iterator, array(
    'title'               => 'Saft API Documentation',
    'theme'               => 'default',
    'build_dir'           => $root . DIRECTORY_SEPARATOR .'gen'. DIRECTORY_SEPARATOR .'apidoc',
    'cache_dir'           => $root . DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .'samicache',
    'include_parent_data' => true,
    'default_opened_level' => 1,
));
