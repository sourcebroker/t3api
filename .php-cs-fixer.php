<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder((new PhpCsFixer\Finder())
        ->in(realpath(__DIR__))
        ->ignoreVCSIgnored(true)
        ->notPath('/^Build\/phpunit\/(UnitTestsBootstrap|FunctionalTestsBootstrap).php/')
        ->notPath('/^Configuration\//')
        ->notPath('/^Documentation\//')
        ->notPath('/^Documentation-GENERATED-temp\//')
        ->notName('/^ext_(emconf|localconf|tables).php/')
    );

return $config;
