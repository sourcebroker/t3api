.. _filtering_sql-in-operator:

SQL "IN" operator
==================

When using query params ``?property=<value>`` only items which match exactly such condition are returned. But it is possible to pass multiple values. If you would like to receive all items which ``property`` matches ``value1`` **or** ``value2`` then you can send ``property`` as an array in query string: ``?property[]=<value1>&property[]=<value2>``. From build-in filters ``NumericFilter`` and ``SearchFilter`` are the filters which support ``IN`` operator.
