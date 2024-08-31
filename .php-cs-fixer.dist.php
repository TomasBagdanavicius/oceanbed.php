<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'single_space_around_construct' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
