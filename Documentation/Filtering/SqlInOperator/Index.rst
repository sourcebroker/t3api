.. _filtering_sql-in-operator:

SQL "IN" operator
==================

When using query params ``?property=<value>`` only items which match exactly such condition are returned. But it is possible to pass multiple values. If you would like to receive all items which ``property`` matches ``value1`` **or** ``value2`` then you can send ``property`` as an array in query string: ``?property[]=<value1>&property[]=<value2>``. From build-in filters ``NumericFilter``, ``UidFilter``, ``SearchFilter`` and ``ContainFilter`` are the filters which support ``IN`` operator.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get news stored on page 3 or 6 (``NumericFilter``):
     | `https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6 <https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6>`__
     |
   * | Get news with uid 1 or 5 (``UidFilter``):
     | `https://14.t3api.ddev.site/_api/news/news?uid[]=1&uid[]=5 <https://14.t3api.ddev.site/_api/news/news?uid[]=1&uid[]=5>`__
     |
