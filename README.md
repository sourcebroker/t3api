# TYPO3 Extension t3api

[![Latest Stable Version](https://poser.pugx.org/sourcebroker/t3api/v/stable)](https://extensions.typo3.org/extension/t3api/)
[![Tests](https://img.shields.io/github/actions/workflow/status/sourcebroker/t3api/tests.yml?branch=main&label=Tests&logo=github)](https://github.com/sourcebroker/t3api/actions/workflows/tests.yml)

Supports TYPO3 12.4, 13.4 and 14 on PHP 8.1 - 8.5.

## Features

- Support for Extbase models with GET, POST, PATCH, PUT, DELETE operations.
- Configuration with classes, properties and methods annotations.
- Built-in filters: boolean, numeric, uid, contain, order, range, distance and text (partial, match against and exact strategies).
- Ordered UIDs filter and query modifiers - return collections in externally resolved order (e.g. from search engine).
- Built-in pagination.
- Opt-in response caching with automatic tag-based invalidation.
- Support for typolinks.
- Support for image processing.
- Support for file uploads (FAL).
- Configurable routing.
- Responses in [Hydra](https://www.hydra-cg.com/) / [JSON-LD](https://json-ld.org/) format.
- Serialization contexts - customizable output depending on routing.
- Easy customizable serialization handlers and subscribers.
- Backend module with Swagger for documentation and real testing.

## Documentation

Read the docs at https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/

## Take a look and test

After cloning repo you can run:

```bash
ddev restart && ddev composer install
ddev ci 14
```

to install local integration test instance. Local instance is available at https://14.t3api.ddev.site/
(login to backend with `admin` / `Password1!` credentials).

At frontend part you can at once test REST API responses for ext news:

- https://14.t3api.ddev.site/_api/news/news
- https://14.t3api.ddev.site/_api/news/news/1
- https://14.t3api.ddev.site/_api/news/categories
- etc

You can also run Postman test with `ddev composer ci:tests:postman` command or full test suite with `ddev composer ci`.
Postman is doing full CRUD test with category and news (with image).

## Development

If you want to help with development take a look at https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/Miscellaneous/Development/Index.html
