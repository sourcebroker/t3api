.. include:: ../../Includes.txt

.. _customization_expression-language:

Expression Language
=====================

`Symfony expression language <https://github.com/symfony/expression-language>`__ is `widely used in TYPO3 core <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html>`__. T3api also uses it in two places:

- :ref:`Serialization (and deserialization) <serialization>`
- :ref:`Security (checking access to operations and filters) <security>`

T3api utilizes TYPO3 core expression language feature. So, if you would like to extend expression language used in t3api core, you need to register new provider in `the same way as you do it e.g. for TS <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html#registering-new-provider-within-an-extension>`__. Just notice that appropriate context has to be specified as array key (in this case it's ``t3api``). Code below shows how to register ``MyCustomProvider`` in ``t3api`` context.

.. code-block:: php
   :caption: typo3conf/ext/my_ext/Configuration/ExpressionLanguage.php

   return [
        't3api' => [
            \Vendor\MyExt\ExpressionLanguage\MyCustomProvider::class,
        ],
   ];

After registration of custom provider follow TYPO3 documentation how to `add additional variables <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html#additional-variables>`__ and `additional functions <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html#additional-functions>`__ into expression language. Now you can use your custom variables and functions inside t3api security check or serialization and deserialization expressions.
