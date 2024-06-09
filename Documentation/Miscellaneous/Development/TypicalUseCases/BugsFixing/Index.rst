.. _development_typical_use_cases_bugsfixing:

============
Bugs Fixing
============

..  rst-class:: bignums-xxl

1. Fork

   Make your fork of t3api on github https://github.com/sourcebroker/t3api

2. Clone

   Clone your forked repo locally.

3. Run ddev

   :bash:`ddev restart`

4. Test CI locally

   Check if project is working correctly before adding your changes.

   Run: :bash:`ddev ci`

5. Branch

   Create branch in your repo.

6. Fix bug

   You can use existing integration testing instances under url:
   :uri:`https://[T3_VERSION].t3api.ddev.site` to make some fast manual testing
   if your bug needs that. Open https://t3api.ddev.site to get the user/password.

   Look at folder :folder:`.ddev/test/`. There are files that builds the integration
   instances. You can try to modify those files to test your bugfix in real env.
   You have there two extensions that can be helpful for testing.

   1. First extension is :folder:`site`, which is regular TYPO3 local mods extension.

   2. Second extension is :folder:`t3apinews`, which is extension that expose
      `ext:news` models. To test it open:

      * https://12.t3api.ddev.site/_api
      * https://12.t3api.ddev.site/_api/news/news
      * https://12.t3api.ddev.site/_api/news/news/1
      * https://12.t3api.ddev.site/_api/news/categories


7. Automated fixes and tests

   If you think you are ready with your bug then:

   * run automated fixes: :bash:`ddev fix`
   * run automated test on current TYPO3: :bash:`ddev ci`

   If all is ok then you can run full matrix test as it will be done on github.

   * :bash:`ddev ci all`

8. Commit and make PR

   * :bash:`git commit -m "BUGFIX: Fixing bug"`
