.. _development_typical_use_cases_bugsfixing:

============
Bugs Fixing
============

..  rst-class:: bignums

1. Fork

   Make your fork of t3api on github https://github.com/sourcebroker/t3api

2. Clone

   Clone your forked repo locally.

3. First run of ddev

   Go inside cloned repo and run: :bash:`ddev restart`

4. Install and make init test

   Inside cloned repo run: :bash:`ddev ci 12`

   This will install project locally (for TYPO3 12) and make tests to check if your
   installed version is working well before you start to modify it.

5. Branch

   Create branch in your repo.

6. Fix bug

   When you modify code of t3api then a symlinked version at :folder:`.test/[T3_VERSION]/src/t3api`
   is also modified and you can access your modified version of t3api at fully working TYPO3
   :uri:`https://[T3_VERSION].t3api.ddev.site`

   Open https://t3api.ddev.site to get overview on user/password to backend.

   Look at :folder:`.test/[T3_VERSION]/src/`.
   Except `t3api` you have there two extensions that can be helpful for testing.

   1. First extension is :folder:`site`, which is regular TYPO3 local mods extension.

   2. Second extension is :folder:`t3apinews`, which is extension that expose
      extension `news` models and is supposed to have only mods for news.
      To test it open:

      * https://12.t3api.ddev.site/_api
      * https://12.t3api.ddev.site/_api/news/news
      * https://12.t3api.ddev.site/_api/news/news/1
      * https://12.t3api.ddev.site/_api/news/categories
      * etc

   Sometimes you may want to flush cache for the TYPO3 located at :folder:`.test/[T3_VERSION]/`.
   Of course you can do :bash:`ddev exec "cd .test/[T3_VERSION] && ./vendor/bin/typo3 cache:flush"`.
   But you can also use special ddev command :bash:`ddev cache-flush`

7. Documentation

   Run :bash:`ddev docs` to run documentation in watch mode. Browser should open automatically.
   You can modify files at :folder:`Documentation` and watch in real time how the docs will
   looks like.
   Look at https://docs.typo3.org/m/typo3/docs-how-to-document/main/en-us/Index.html for info about
   formatting possibilities.

8. Automated fixes and tests

   If you think you are ready with your bug then:

   * run automated fixes: :bash:`ddev fix`
   * run automated test on current TYPO3: :bash:`ddev ci`

   If all is ok then you can run full matrix test as it will be done on github.

   * :bash:`ddev ci all`

9. Commit, push and make PR

   https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request

