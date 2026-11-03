<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;

/**
 * Records of the resource are available only on its storage page - configuration from the Security documentation
 * (`storagePid` limits only the collection, item operations are limited by `security`).
 *
 * @ApiResource(
 *     attributes={
 *         "persistence": {"storagePid": "3"}
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/stored-categories"
 *         },
 *         "post": {
 *             "method": "POST",
 *             "path": "/stored-categories",
 *             "denormalizationContext": {"groups": {"api_recipe_write"}}
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/stored-categories/{id}",
 *             "security": "object.getPid() in t3apiOperation.getPersistenceSettings().getStoragePids()"
 *         },
 *         "patch": {
 *             "method": "PATCH",
 *             "path": "/stored-categories/{id}",
 *             "security": "object.getPid() in t3apiOperation.getPersistenceSettings().getStoragePids()",
 *             "denormalizationContext": {"groups": {"api_recipe_write"}}
 *         },
 *         "put": {
 *             "method": "PUT",
 *             "path": "/stored-categories/{id}",
 *             "security": "object.getPid() in t3apiOperation.getPersistenceSettings().getStoragePids()",
 *             "denormalizationContext": {"groups": {"api_recipe_write"}}
 *         },
 *         "delete": {
 *             "method": "DELETE",
 *             "path": "/stored-categories/{id}",
 *             "security": "object.getPid() in t3apiOperation.getPersistenceSettings().getStoragePids()"
 *         },
 *         "get_unrestricted": {
 *             "path": "/unrestricted-stored-categories/{id}"
 *         }
 *     }
 * )
 */
class StoredCategory extends Category {}
