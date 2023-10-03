.. include:: ../Includes.txt

.. _common_issues:

=====================================
Common Issues
=====================================

"&cHash empty" issue
==========================================================

   Request parameters could not be validated (&cHash empty)

If you are receiving such TYPO3's error when trying to access endpoints potential reasons could be:

- t3api version lower than 2.1.
- TYPO3 global configuration or one of the installed extensions is resetting ``cacheHash.excludedParameters`` value. Check if ``t3apiResource`` is inside collection of excluded parameters defined in ``$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters']``. Such value is added by t3api core so you should use merge ``excludedParameters`` instead of override.
