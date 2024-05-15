<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$finder = $config->getFinder();

$finder->in(__DIR__)
    ->exclude('Tests/Postman');

return $config;
