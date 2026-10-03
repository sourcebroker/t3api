<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\String_;
use Rector\Config\RectorConfig;
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Php85\Rector\Class_\SleepToSerializeRector;
use Rector\Php85\Rector\Class_\WakeupToUnserializeRector;
use Rector\Php85\Rector\ClassMethod\NullDebugInfoReturnRector;
use Rector\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector;
use Rector\Php85\Rector\FuncCall\ChrArgModuloRector;
use Rector\Php85\Rector\FuncCall\OrdSingleByteRector;
use Rector\Php85\Rector\FuncCall\RemoveFinfoBufferContextArgRector;
use Rector\Php85\Rector\ShellExec\ShellExecFunctionCallOverBackticksRector;
use Rector\Php85\Rector\Switch_\ColonAfterSwitchCaseRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\Cast\RenameCastRector;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Renaming\ValueObject\RenameCast;
use Ssch\TYPO3Rector\Set\Typo3SetList;

/**
 * Compatibility check (run in dry-run mode in CI) for TYPO3 12 - 14 and PHP 8.1 - 8.5.
 *
 * Only deprecation fixes are enabled for PHP 8.4 / 8.5. Rules that introduce syntax
 * or functions not available in PHP 8.1 (array_first(), new without parentheses,
 * #[\Override] on properties etc.) are deliberately left out.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../Classes',
        __DIR__ . '/../../Configuration',
        __DIR__ . '/../../Tests',
        __DIR__ . '/../../ext_emconf.php',
        __DIR__ . '/../../ext_localconf.php',
    ])
    ->withSets([
        Typo3SetList::TYPO3_12,
        Typo3SetList::TYPO3_13,
        Typo3SetList::TYPO3_14,
    ])
    ->withRules([
        // PHP 8.4 deprecations
        ExplicitNullableParamTypeRector::class,
        AddEscapeArgumentRector::class,
        // PHP 8.5 deprecations
        ArrayKeyExistsNullToEmptyStringRector::class,
        ChrArgModuloRector::class,
        ColonAfterSwitchCaseRector::class,
        NullDebugInfoReturnRector::class,
        OrdSingleByteRector::class,
        RemoveFinfoBufferContextArgRector::class,
        ShellExecFunctionCallOverBackticksRector::class,
        SleepToSerializeRector::class,
        WakeupToUnserializeRector::class,
    ])
    ->withConfiguredRule(RenameCastRector::class, [
        new RenameCast(Int_::class, Int_::KIND_INTEGER, Int_::KIND_INT),
        new RenameCast(Bool_::class, Bool_::KIND_BOOLEAN, Bool_::KIND_BOOL),
        new RenameCast(Double::class, Double::KIND_DOUBLE, Double::KIND_FLOAT),
        new RenameCast(String_::class, String_::KIND_BINARY, String_::KIND_STRING),
    ])
    ->withConfiguredRule(RemoveFuncCallRector::class, ['curl_close', 'curl_share_close', 'finfo_close', 'imagedestroy', 'xml_parser_free'])
    ->withConfiguredRule(RenameConstantRector::class, ['FILTER_DEFAULT' => 'FILTER_UNSAFE_RAW'])
    ->withSkip([
        // @todo Remove when support for TYPO3 v13 is dropped. Menu::makeMenuItem() is deprecated in v14,
        //  but ComponentFactory does not exist in v12 / v13.
        \Ssch\TYPO3Rector\TYPO314\v0\MigrateButtonBarMenuAndMenuRegistryMakeMethodsToComponentFactoryRector::class => [
            __DIR__ . '/../../Classes/Controller/AdministrationController.php',
        ],
    ])
    ->withCache(__DIR__ . '/../../.Build/var/cache/rector');
