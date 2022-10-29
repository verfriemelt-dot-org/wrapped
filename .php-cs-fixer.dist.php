<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/_'])
    ->in([__DIR__ . '/tests'])

;

$config = new PhpCsFixer\Config();
$config->setRules([
    '@Symfony' => true,
    '@PHP81Migration' => true,

    'php_unit_test_case_static_method_calls' => true,
    'declare_strict_types' => true,
    'php_unit_strict' => true,

    // overwrite some symfony defaults
    'blank_line_before_statement' => false,
    'self_accessor' => false,
    'phpdoc_annotation_without_dot' => true,
    'phpdoc_summary' => false,
    'single_line_throw' => false,
    'concat_space' => ['spacing' => 'one'],

    // away you go
    'yoda_style' => false,

]);
$config->setFinder($finder);
$config->setRiskyAllowed(true);
$config->setUsingCache(false);


return $config;
