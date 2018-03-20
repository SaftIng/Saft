<?php

$header = <<<'EOF'
This file is part of Backbone.

Copyright (c) by
- Konrad Abicht <konrad.abicht@pier-and-peer.com> and
- Nico Seifert <nico.seifert@pier-and-peer.com>

EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(false)
    ->setRules(
        [
            '@Symfony' => true,
            'array_syntax' => ['syntax' => 'short'],
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->files()
        ->in(__DIR__ . '/src')
        ->name('*.php')
    );
