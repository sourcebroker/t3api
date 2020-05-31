.. include:: ../../Includes.txt

.. _filtering_filters_distance-filter:

DistanceFilter
===============

Distance filter allows to filter map points points by radius. Map points kept in the database needs to contain latitude and longitude to use this filter.

Configuration for distance filter looks a little bit different than for other build-in filter. Because distance filter is not based on single field it should not contain ``properties`` definition. Instead of that it is needed to specify which model properties contain latitude and longitude in ``arguments``. Moreover, as ``properties`` is not defined, ``parameterName`` is required. Beside default values in ``arguments``, distance filter accepts also:

- ``latProperty`` (``string``) - Name of the property which holds latitude
- ``lngProperty`` (``string``) - name of the property which holds longitude
- ``unit`` (ENUM: "mi", "km"; default "km") - Unit of the radius
- ``radius`` (``float/int``; default ``100``) - Radius to filter in; if ``allowClientRadius`` is set to ``true``, then used as default value.
- ``allowClientRadius`` (``bool``; default ``false``) - Set to ``true`` allow to change the radius from GET parameter.

.. code-block:: php

    use SourceBroker\T3api\Filter\DistanceFilter;

    /**
     * @T3api\ApiFilter(
     *     DistanceFilter::class,
     *     arguments={
     *          "parameterName"="position",
     *          "latProperty"="gpsLatitude",
     *          "lngProperty"="gpsLongitude",
     *          "radius"="100",
     *          "unit"="km",
     *     }
     * )
     */
    class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

