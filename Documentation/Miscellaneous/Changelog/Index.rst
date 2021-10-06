.. include:: ../Includes.txt

.. _changelog:

=====================================
Changelog
=====================================

1.2.2
=====
- Fixes composer dependencies conflict regarding doctrine/cache [`pull request <https://github.com/sourcebroker/t3api/pull/43>`__; `discussion <https://github.com/sourcebroker/t3api/discussions/37>`__]
- Unifies expression language context for all usages [`84cb085 <https://github.com/sourcebroker/t3api/commit/84cb085a3de05040682aae9b5ed7c916a06ac21c>`__; `85c2455 <https://github.com/sourcebroker/t3api/commit/85c2455baa93a2f6e37f015444ab49a7d3bee629>`__]
- Differentiates cache for API resource according to site's and API base path [`f54843c <https://github.com/sourcebroker/t3api/commit/f54843cac428d529b9dbfdf35e7cf84f0520c5f2>`__]
- Adds possibility to set different serializer context attributes for serialization and deserialization [`bbf104d <https://github.com/sourcebroker/t3api/commit/bbf104d99be8a645bda122ff90d702136e4c4a38>`__]

1.2
====

- Implements support for CORS configuration [`pull request <https://github.com/sourcebroker/t3api/pull/28>`__] [:ref:`doc <cors>`]
- Implements request processors and moves language handling into processor [`2f9a75e <https://github.com/sourcebroker/t3api/commit/2f9a75e1683c857b2326ced041e84870bcc170f9>`__]
- Base development version on DDEV
- Implements easy and safe way to assign current frontend user to records [`56a38d7 <https://github.com/sourcebroker/t3api/commit/56a38d7179d30a4f5937f837f776b5dfb72bd2d1>`__] [:ref:`doc <use-cases_current-user-assignment>`]
- Adds support for RTE on serialization [`3c09f5 <https://github.com/sourcebroker/t3api/commit/3c09f5b3abe39112cb4d36a69d6cd1e559551fd7>`__]
- Adds support for password hashing on serialization [`78dcbef <https://github.com/sourcebroker/t3api/commit/78dcbef6ebb3e573292bfd122ee6b3f4bcdd80c9>`__]
- Fixes current site resolver
- Extends backend module by site selector [`3389936 <https://github.com/sourcebroker/t3api/commit/33899360d2f20950072e5a0d02169435faed7ddc>`__]
