.. _customization_api-resource-path:

Api Resource Path
=================

By default t3api will search for API Resource classes in
:folder:`Classes/Domain/Model/*.php` of currently loaded extensions. This behaviour
is defined in :class:`LoadedExtensionsDomainModelApiResourcePathProvider`
and registered in :file:`ext_localconf.php` like this:

.. code-block:: php

      $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['apiResourcePathProviders'] = [
            \SourceBroker\T3api\Provider\ApiResourcePath\LoadedExtensionsDomainModelApiResourcePathProvider::class,
        ];

The same way you can add your own providers for additional patches.
