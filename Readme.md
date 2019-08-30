# TYPO3 Extension ``t3api``

[![Latest Stable Version](https://poser.pugx.org/sourcebroker/t3api/v/stable)](https://packagist.org/packages/sourcebroker/t3api)
[![License](https://poser.pugx.org/sourcebroker/t3api/license)](https://packagist.org/packages/sourcebroker/t3api)

This extension provides REST API for Extbase models of your TYPO3 project.

## 1. Features

- Support for Extbase models.
- Configuration with classes, properties and methods annotations.
- Build-in filters: boolean, numeric, order, range and text (partial, match against and exact strategies).
- Build-in pagination.
- Support for TypoLinks and image processing.
- Configurable routing.
- Responses in Hydra/[JSON-LD](https://json-ld.org/) format.
- Serialization contexts - customizable output depending on routing.
- Easy customizable serialization handlers and subscribers.
- Support for all features of [JMSSerializer](https://jmsyst.com/libs/serializer).
- [Well documented](https://docs.typo3.org/typo3cms/extensions/t3api/).

## 2. Usage

### 1) Installation

#### Composer

Installation by composer is recommended.
In your Composer based TYPO3 project root, just do `composer require sourcebroker/t3api`. 

#### TYPO3 Extension Repository (TER)

Download and install the extension with the extension manager module.

### 2) Minimal setup

Add route enhancer to your site `config.yaml` file. `basePath` is the prefix for all api endpoints.

```yaml
routeEnhancers:
  T3api:
    type: T3apiResourceEnhancer
    basePath: '_api'
```

Configure routes for your Extbase model using PHP annotations:

```php
/**
 * @SourceBroker\T3api\Annotation\ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/articles",
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/articles/{id}",
 *          }
 *     },
 * )
 */
class Article extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
}
```

To check more configuration options visit
[official documentation](https://docs.typo3.org/typo3cms/extensions/t3api/)
or [example integration of t3api for well known `news` extension](https://github.com/sourcebroker/t3apinews).

## 3. Administration corner

### 3.1. Versions and support

| T3api       | TYPO3      | PHP       | Support/Development                     |
| ----------- | ---------- | ----------|---------------------------------------- |
| 0.1.x       | 9.x        | 7.2 - 7.3 | Features, Bugfixes, Security Updates    |

### 3.2. Release Management

T3api uses **semantic versioning** which basically means for you, that
- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.



