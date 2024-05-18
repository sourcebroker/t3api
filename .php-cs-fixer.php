<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();

$config->setFinder(
    (new PhpCsFixer\Finder())
        ->in(realpath(__DIR__))
            ->ignoreVCSIgnored(true)
            ->notPath('/^Tests\/Postman\//')
            ->notPath('/^Build\/phpunit\/(UnitTestsBootstrap|FunctionalTestsBootstrap).php/')
            ->notPath('/^Configuration\//')
            ->notPath('/^Documentation\//')
            ->notPath('/^Documentation-GENERATED-temp\//')
            ->notName('/^ext_(emconf|localconf|tables).php/')
        );

return $config;
