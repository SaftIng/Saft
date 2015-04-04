<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$root = realpath(__DIR__);
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($root.'/src')
;

return new Sami($iterator, array(
    'title'               => 'Saft API Documentation',
    'theme'               => 'enhanced',
    'build_dir'           => "$root/gen/apidoc",
    'cache_dir'           => "$root/tmp/samicache",
    'include_parent_data' => true,
    'default_opened_level' => 1,
));
