.. _integration:

============
Integration
============

@todo - write docs

Integration with other extensions
====================================

@todo - write docs
@todo - write docs: configure serializer for classes which can not be override :ref:`serialization_yaml_metadata`

News extension - Example integration
======================================

@todo - write docs t3apinews

Inline output
======================================

@todo - write docs: If you would like to include your JSON directly in TYPO3 HTML output (e.g. to omit waiting for initial request
to API) you can use xxxViewHelper as follows:

.. code-block:: html

    <html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:t3api="http://typo3.org/ns/SourceBroker/T3api/ViewHelpers"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      data-namespace-typo3-fluid="true">

        <script type="application/json">
            <t3api:xxx />
        </script>

    </html>
