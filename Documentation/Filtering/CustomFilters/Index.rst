.. _filtering_custom-filters:

==============
Custom filters
==============

It is super easy to create custom filters which will match your specific requirements.
Custom filters has to implement interface ``\SourceBroker\T3api\Filter\FilterInterface`` and contain one public method ``filterProperty``.
Method ``filterProperty`` accepts 4 arguments:

- $property (``string``) - Name of the property to filter by.
- $values (``mixed``) - Values passed in request.
- $query (``TYPO3\CMS\Extbase\Persistence\QueryInterface``) - Instance of Extbase's query.
- $apiFilter (``SourceBroker\T3api\Domain\Model\ApiFilter``) - Instance of T3api's filter.

.. important::
    Method ``filterProperty`` has to return ``null`` or instance of ``TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface``.
    If it returns null it won't affect the final result at all. If it returns ``ConstraintInterface`` returned constraint will be added to final query.

.. note::
    Keep in mind that second argument (``$values``) is mixed type. Depending on requested URL it can be array or string.
    If requests's query string contains ``property=hello`` then ``$values`` will be a string ``hello``.
    If requests's query string contains ``property[]=hello`` then ``$values`` will be a array with one string inside (``hello``).
    Consider casting ``$values`` to array inside your filter to avoid bugs (``$values = (array)$values;``).

Example implementation of custom filter may looks as follows:

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Extension\Filter;

   use SourceBroker\T3api\Domain\Model\ApiFilter;
   use SourceBroker\T3api\Filter\FilterInterface;
   use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
   use TYPO3\CMS\Extbase\Persistence\QueryInterface;

   class MyCustomFilter implements FilterInterface
   {
       public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
       {
           return $query->equals($property, $values);
       }
   }

It may be useful, but not required, to extend class ``\SourceBroker\T3api\Filter\AbstractFilter`` which will give you bunch of methods inside your filter. An example of such method may be ``addJoinsForNestedProperty`` which may be really useful to handle more complex filters, especially when you need to implement something which is out of reach Extbase's query builder. Check code inside ``\SourceBroker\T3api\Filter\ContainFilter`` and ``\SourceBroker\T3api\Filter\SearchFilter`` for example usages.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Extension\Filter;

   use SourceBroker\T3api\Domain\Model\ApiFilter;
   use SourceBroker\T3api\Filter\AbstractFilter;
   use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
   use TYPO3\CMS\Extbase\Persistence\QueryInterface;

   class MyCustomFilter extends AbstractFilter
   {
       public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
       {
           // ...
       }
   }

.. note::
    Instance of your custom filter will be created using Extbase's ObjectManager, so you can inject into it any other services if you need them.

To use your new custom filter is it just needed to pass it as first parameter into ``@ApiFilter`` annotation:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use Vendor\Extension\Filter\MyCustomFilter;

   /**
    * @T3api\ApiFilter(
    *     MyCustomFilter::class,
    *     properties={"username"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }
