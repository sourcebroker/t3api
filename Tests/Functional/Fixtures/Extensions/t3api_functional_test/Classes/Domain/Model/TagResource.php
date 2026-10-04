<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;

/**
 * API resource extending a model which is not an API resource - the same way as API resources
 * extend models of 3rd party extensions. `Product` relations are declared with this class
 * only in serializer YAML metadata (see Resources/Private/Serializer), while Extbase still
 * creates `Tag` objects.
 *
 * @ApiResource(
 *     itemOperations={
 *         "get": {
 *             "path": "/tags/{id}"
 *         }
 *     }
 * )
 */
class TagResource extends Tag {}
