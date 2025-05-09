<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->exclude([
        'config/secrets',
        'var',
        'vendor',
    ])
    ->notPath([
        'config/preload.php',
        'public/index.php',
        'tests/bootstrap.php',
    ])
    ->in(__DIR__)
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'comment_to_phpdoc' => [
            'ignored_tags' => [],
        ], // @PhpCsFixer:risky default
        'concat_space' => [
            'spacing' => 'one',
        ], // overrides @Symfony defaults
        'multiline_comment_opening_closing' => true, // @PhpCsFixer default
        'no_superfluous_phpdoc_tags' => [
            'allow_hidden_params' => true,
            'remove_inheritdoc' => false,  // overrides @Symfony defaults
        ],
        'single_line_throw' => false, // overrides @Symfony defaults
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'arguments',
                'arrays',
                'match',
                'parameters',
            ],
        ], // overrides @Symfony defaults
    ])
    ->setFinder($finder)
    ->setParallelConfig(ParallelConfigFactory::detect())
;
