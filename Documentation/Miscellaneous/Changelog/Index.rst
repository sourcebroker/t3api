.. _changelog:

=====================================
Changelog
=====================================

3.0.0
====
- [!!!] Changes signal slots into PSR-14 events [`issue <https://github.com/sourcebroker/t3api/issues/67>`__]
- Protect against "&cHash empty" error when ``cacheHash.enforceValidation`` is set to ``true`` [`issue <https://github.com/sourcebroker/t3api/issues/81>`__]
- Add testing instance for TYPO3 12, remove testing instance for TYPO3 10. Change PHP to 8.1 for testing instances.
- [!!!] Drop TYPO3 10, TYPO3 11 on dependencies. Update dependencies to TYPO3 12.
- Move changing language request set in header X-Locale to earlier stage, before "typo3/cms-frontend/tsfe". Add support for setting language of api request by standard language prefix instead of header X-Locale. [`commit <https://github.com/sourcebroker/t3api/commit/c36be252>`__]
- Prevent FileReferenceHandler and ImageHandler from throwing error on problems with processing, missing file etc. [`commit <https://github.com/sourcebroker/t3api/commit/b2f4c63a>`__]
- Add crop support for ImageHandler [`commit <https://github.com/sourcebroker/t3api/commit/a7481f41>`__]
- Add essential meta information to the OpenAPI spec [`commit <https://github.com/sourcebroker/t3api/commit/f7b3c1ef>`__]
- Do not convert empty string to absolute url [`commit <https://github.com/sourcebroker/t3api/commit/0518d3d4>`__]
- Refactor link generation. Replace getTypoLink_URL with linkFactory. [`commit <https://github.com/sourcebroker/t3api/commit/47f9bbfa>`__]
- Add support for graceful exception handling for serialization process in TYPO3 [`commit <https://github.com/sourcebroker/t3api/commit/ea6bae2c>`__]
- Add optional @type attribute for resource. As this is rarely used, and can influence size of response, it must be activated by adding a subscriber to serializerSubscribers array. [`commit <https://github.com/sourcebroker/t3api/commit/32b73726>`__]

2.0.3
=====
- Extend dependency for symfony/cache to prevent t3api from downgrading symfony/cache in TYPO3 11 environments [`commit <https://github.com/sourcebroker/t3api/commit/b053679>`__]

2.0.2
=====
- PHP 8.1 fix warning about undefined array key "cacheCmd" [`commit <https://github.com/sourcebroker/t3api/commit/1eaf111>`__]
- Add PHP 8.1 to CI matrix. [`commit <https://github.com/sourcebroker/t3api/commit/e8306d21>`__]
- Update local installer [`commit <https://github.com/sourcebroker/t3api/commit/5fa2ba12>`__]

2.0.1
=====
- Changes signal slot parameters structure to indexed array instead of associative for slots afterProcessOperation and afterDeserializeOperation [`commit <https://github.com/sourcebroker/t3api/commit/1104c97>`__]
- Excludes property from serialization to avoid an error on serialize process [`commit <https://github.com/sourcebroker/t3api/commit/1ec116c>`__]

2.0.0
=====
- Move from travis to github actions [`commit <https://github.com/sourcebroker/t3api/commit/1e10ad6b>`__]
- Make php-cs-fixer config form TYPO3/coding-standards. Update .editorconfig from TYPO3/coding-standards [`commit <https://github.com/sourcebroker/t3api/commit/6a36dd3d>`__]
- Refactor ddev for test instances. [`commit <https://github.com/sourcebroker/t3api/commit/96e2903f>`__]
- Adds support for TYPO3 v11 [`issue <https://github.com/sourcebroker/t3api/issues/48>`__]
- Makes it possible to include custom paths as API resources [`issue <https://github.com/sourcebroker/t3api/issues/22>`__]
- Adds support for API resources inside subdirectories of ``Classes/Domain/Model/`` [`issue <https://github.com/sourcebroker/t3api/issues/22>`__]
- [!!!] Drops support for TYPO3 < 10.4
- [!!!] Changes annotation ``ReadOnly`` to ``ReadOnlyProperty`` [`commit 1 <https://github.com/sourcebroker/t3api/commit/dc51a69c2b09edfb429a31e687b94cbcd267c8ff>`__; `commit 2 <https://github.com/sourcebroker/t3api/commit/1c1bbc99121ddf8d4f6bbb8da2ff18dd461227f0>`__]
- [!!!] Changes signal slot ``\SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES`` parameters structure to indexed array instead of associative [`commit <https://github.com/sourcebroker/t3api/commit/6eb56b9161956150c654be1421ca05dbc17ec3b0>`__]

1.2.3
=====
- Handle edge cases for forcing absolute url. [`issue <https://github.com/sourcebroker/t3api/issues/17>`__; `aeb7081 <https://github.com/sourcebroker/t3api/commit/aeb708154cd957fc79d576a88b11faaeaca40ade>`__]
- Handle case when file is not available on system file. [`issue <https://github.com/sourcebroker/t3api/issues/33>`__; `2765ccf <https://github.com/sourcebroker/t3api/commit/2765ccf5105ca47c6c005292aa15c29ef17ca200>`__; `11be8dd <https://github.com/sourcebroker/t3api/commit/11be8ddd53018fc85d4f7cdeaec6688207607ec2>`__; `53af4ab <https://github.com/sourcebroker/t3api/commit/53af4ab669afac0d9103eef56dafd38a0b866d80>`__]
- Handle edge cases for combining url and host in UrlService::forceAbsoluteUrl [`099df00 <https://github.com/sourcebroker/t3api/commit/099df001009c1b111b01031be04202df8f4ba8f9>`__]

1.2.2
=====
- Fixes composer dependencies conflict regarding doctrine/cache [`pull request <https://github.com/sourcebroker/t3api/pull/43>`__; `discussion <https://github.com/sourcebroker/t3api/discussions/37>`__]
- Unifies expression language context for all usages [`84cb085 <https://github.com/sourcebroker/t3api/commit/84cb085a3de05040682aae9b5ed7c916a06ac21c>`__; `85c2455 <https://github.com/sourcebroker/t3api/commit/85c2455baa93a2f6e37f015444ab49a7d3bee629>`__]
- Differentiates cache for API resource according to site's and API base path [`f54843c <https://github.com/sourcebroker/t3api/commit/f54843cac428d529b9dbfdf35e7cf84f0520c5f2>`__]
- Adds possibility to set different serializer context attributes for serialization and deserialization [`bbf104d <https://github.com/sourcebroker/t3api/commit/bbf104d99be8a645bda122ff90d702136e4c4a38>`__]

1.2.0
=====

- Implements support for CORS configuration [`pull request <https://github.com/sourcebroker/t3api/pull/28>`__] [:ref:`doc <cors>`]
- Implements request processors and moves language handling into processor [`2f9a75e <https://github.com/sourcebroker/t3api/commit/2f9a75e1683c857b2326ced041e84870bcc170f9>`__]
- Base development version on DDEV
- Implements easy and safe way to assign current frontend user to records [`56a38d7 <https://github.com/sourcebroker/t3api/commit/56a38d7179d30a4f5937f837f776b5dfb72bd2d1>`__] [:ref:`doc <use-cases_current-user-assignment>`]
- Adds support for RTE on serialization [`3c09f5 <https://github.com/sourcebroker/t3api/commit/3c09f5b3abe39112cb4d36a69d6cd1e559551fd7>`__]
- Adds support for password hashing on serialization [`78dcbef <https://github.com/sourcebroker/t3api/commit/78dcbef6ebb3e573292bfd122ee6b3f4bcdd80c9>`__]
- Fixes current site resolver
- Extends backend module by site selector [`3389936 <https://github.com/sourcebroker/t3api/commit/33899360d2f20950072e5a0d02169435faed7ddc>`__]
