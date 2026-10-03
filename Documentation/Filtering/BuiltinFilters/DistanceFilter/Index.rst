.. _filtering_filters_distance-filter:

DistanceFilter
===============

Distance filter allows to filter map points by radius. Map points kept in the database needs to contain latitude and longitude to use this filter.

Configuration for distance filter looks a little bit different than for other build-in filter. Because distance filter is not based on single field it should not contain ``properties`` definition. Instead of that it is needed to specify which model properties contain latitude and longitude in ``arguments``. Moreover, as ``properties`` is not defined, ``parameterName`` is required. Beside default values in ``arguments``, distance filter accepts also:

- ``latProperty`` (``string``) - Name of the property which holds latitude
- ``lngProperty`` (``string``) - name of the property which holds longitude
- ``unit`` (ENUM: "mi", "km"; default "km") - Unit of the radius
- ``radius`` (``float/int``; default ``100``) - Radius to filter in; if ``allowClientRadius`` is set to ``true``, then used as default value.
- ``allowClientRadius`` (``bool``; default ``false``) - Set to ``true`` allow to change the radius from GET parameter.

.. code-block:: php

    use SourceBroker\T3api\Annotation as T3api;
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

Syntax: ``?<parameterName>[lat]=<float>&<parameterName>[lng]=<float>`` and optionally ``&<parameterName>[radius]=<float>``.

With configuration above request ``/items?position[lat]=52.2297&position[lng]=21.0122`` returns items which are not further than 100 km from given point. Both ``lat`` and ``lng`` are required - if one of them is missing an error is returned. ``position[radius]`` is taken into account only if ``allowClientRadius`` is set to ``true``, otherwise it is ignored and ``radius`` from ``arguments`` is used.

Latitude and longitude properties can also be nested (e.g. ``"latProperty"="address.latitude"``).

.. note::

   Distance filter only filters items - it does not order them by distance. If you need ordering by distance use a :ref:`custom filter with query modifier <filtering_custom-filters_query-modifiers>`.
