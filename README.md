# TYPO3 Extension t3api

[![Latest Stable Version](https://poser.pugx.org/sourcebroker/t3api/v/stable)](https://extensions.typo3.org/extension/t3api/)
[![Tests](https://img.shields.io/github/actions/workflow/status/sourcebroker/t3api/tests.yml?branch=main&label=Tests&logo=github)](https://github.com/sourcebroker/t3api/actions/workflows/tests.yml)

REST API for Extbase models. Resources, operations and filters are configured with annotations on the model class
(or on a class extending a model of another extension), output with serializer metadata in annotations or YAML.
Configuration options follow [API Platform](https://api-platform.com), responses use [JSON-LD](https://json-ld.org/) /
[Hydra](https://www.hydra-cg.com/) format.

| TYPO3 | PHP       |
|-------|-----------|
| 12.4  | 8.1 - 8.4 |
| 13.4  | 8.2 - 8.5 |
| 14    | 8.2 - 8.5 |

## Features

**Resources and operations**

- `GET`, `POST`, `PATCH`, `PUT` and `DELETE` operations for Extbase models.
- Configurable routing and base path of the API.
- Cascade persistence of relations, including inline (IRRE) relations.
- Slug fields generated according to TCA configuration.
- Multilanguage support (language prefix in URL or `X-Locale` header).

**Collections**

- Built-in filters: boolean, numeric, uid, contain, order, range, distance and search (partial, match against and exact strategies).
- Ordered UIDs filter and query modifiers - collections in externally resolved order (e.g. from a search engine).
- Pagination.

**Output**

- Serialization contexts (groups) - different output for every operation.
- Serializer metadata in annotations or YAML, custom handlers and subscribers.
- Typolinks, processed images and file references.
- File upload to FAL.

**Access and HTTP**

- Access control of operations and filters with expressions (`security`, `security_post_denormalize`).
- CORS configuration.
- Opt-in response cache with automatic tag-based invalidation.

**Developer tools**

- OpenAPI specification and backend module with Swagger UI for documentation and testing of the API.
- Events to customize processing of operations.

## Quick start

```bash
composer require sourcebroker/t3api
```

Add the route enhancer to the `config.yaml` of your site:

```yaml
imports:
  - { resource: "EXT:t3api/Configuration/Routing/config.yaml" }
```

Make an API resource from an Extbase model:

```php
use SourceBroker\T3api\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"={"path"="/items"},
 *     },
 *     itemOperations={
 *         "get"={"path"="/items/{id}"},
 *     },
 * )
 */
class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
}
```

After flushing caches the items are available under `https://example.com/_api/items` and
`https://example.com/_api/items/1`.

## Documentation

https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/

## Local test instance

After cloning the repository run:

```bash
ddev restart && ddev composer install
ddev ci 14
```

It installs a TYPO3 14 instance at https://14.t3api.ddev.site/ (backend login `admin` / `Password1!`) with
`EXT:news` records exposed as API resources, e.g.:

- https://14.t3api.ddev.site/_api/
- https://14.t3api.ddev.site/_api/news/news
- https://14.t3api.ddev.site/_api/news/news/1
- https://14.t3api.ddev.site/_api/news/categories

Run the Postman tests (full CRUD of news and categories, including image upload) with
`ddev composer ci:tests:postman`, or the full test suite with `ddev composer ci`.

## Development

See https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/Miscellaneous/Development/Index.html
