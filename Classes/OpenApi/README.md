# OpenApi

Embedded copy of [goldspecdigital/oooas](https://github.com/goldspecdigital/oooas) v2.10.0
(MIT, see `LICENSE.md`), used by t3api to build the OpenAPI specification.

The upstream package is no longer maintained (last release in 2022) and emits deprecations
on PHP 8.4+, which TYPO3 in Development context turns into exceptions. Changes against upstream:

- namespace `GoldSpecDigital\ObjectOrientedOAS` renamed to `SourceBroker\T3api\OpenApi`,
- explicit nullable types for parameters with `null` default (PHP 8.4+ deprecation),
- explicit `?? ''` for array keys taken from optional properties (PHP 8.5 deprecation of `null` as array offset),
- JSON schema used by `OpenApi::validate()` moved to `schemas/` next to this file,
- `unset()` of reference variables after `foreach` by reference,
- PHPDoc of variadic parameters fixed and `toArray()` added to `SchemaContract` (PHPStan),
- code style aligned with the rest of t3api (PHP CS Fixer, TYPO3 coding standards).
