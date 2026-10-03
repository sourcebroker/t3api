# Rector

Compatibility check for TYPO3 12 - 14 and PHP 8.1 - 8.5. Runs in dry-run mode as part of `composer ci`
(`composer ci:php:rector`). Use `composer fix:php:rector` to apply changes.

## Why a separate composer.json?

`ssch/typo3-rector` 3.x requires `nikic/php-parser` `^5.7`, but TYPO3 12 (`typo3/cms-install` 12.4)
requires `nikic/php-parser` `^4.15.4`. Both can not be installed together, so having Rector in the root
`require-dev` would break all TYPO3 12 jobs of the test matrix. TYPO3 13.1+ requires `nikic/php-parser` `^5`,
so there is no conflict there.

## @todo When support for TYPO3 12 is dropped

Move Rector into the root `composer.json` and remove this folder:

1. Add `"ssch/typo3-rector": "^3.16"` to `require-dev` of the root `composer.json`.
2. Move `rector.php` to the root folder and adapt paths (`__DIR__ . '/../../Classes'` -> `__DIR__ . '/Classes'` etc.).
3. Change `ci:php:rector` and `fix:php:rector` scripts to use `.Build/bin/rector` and drop the
   `composer install --working-dir=Build/rector` step.
4. Remove `-not -path './Build/rector/vendor/*'` from the `ci:php:lint` script.
5. Remove `Typo3SetList::TYPO3_12` from the sets if no longer needed.
