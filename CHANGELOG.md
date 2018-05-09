# LUYA TEST SUITE

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md).

## 1.0.9 (20. April 2018) and 1.1.0 (26. March 2018)

+ [#7](https://github.com/luyadev/luya-testsuite/issues/7) New ActiveRecordFixture with auto build of table schemas.
+ [#6](https://github.com/luyadev/luya-testsuite/issues/6) NgRest TestCase for Model, API and Controller.
+ Change dependencies for depending LUYA modules.

## 1.0.8 (31. January 2018)

+ Added invokeMethod function to call protected and private methods.
+ Added new assertSameNoSpace(), assertContainsNoSpace() and assertSameTrimmed() methods in BaseTestSuite.
+ Fixed issue where extra vars where not assigned to the admin view in CmsBlockTestcase.

## 1.0.7 (12. January 2018)

+ Fixed bug where debug message could not handle array input.

## 1.0.6 (13. December 2017)

+ [#4](https://github.com/luyadev/luya-testsuite/issues/4) Added luya core dependency.
+ [#5](https://github.com/luyadev/luya-testsuite/issues/5) Added test case for cms blocks.
+ Fixed issue with wrong formatted debug url.

## 1.0.5 (9. September 2017)

+ Added Yii default debug informations (print exceptions by default now).
+ Added trim compare function to base test suite.
+ Removed LUYA Core RC3 depencie which allows to used in other releases and version.

## 1.0.4 (28. Jun 2017)

+ Fixed problem with post request where urls are not handled.
+ Added array notation for path with params `['path/', 'foo' => 'bar']`.
+ Added phpdocs

## 1.0.3 (19. May 2017)

+ Added new methods for `luya\testsuite\cases\ServerTestCase`.
+ Renamed `isUrlNOK` to `assertUrlIsError` in `luya\testsuite\cases\ServerTestCase`
+ Renamed `isUrlOK` to `assertUrlIsOk` in `luya\testsuite\cases\ServerTestCase`
+ Make ServerTesteCase available for multiple tests (kill timeout bug).

## 1.0.2 (17. May 2017)

+ Added `luya\testsuite\cases\ConsoleApplicationTestCase` Starts a console application.
+ Added `luya\testsuite\cases\ServerTestCase` in order to make http request on the current Website. "Browser Testing".

## 1.0.1 (17. May 2017)

+ Added `luya\testsuite\traits\MessageFileCompareTrait` to compare a messages folder based on a master language.
+ Added `luya\testsuite\traits\MigrationFileCheckTrait` to make sure a migration file has no exception and contains up and down commands.
+ Prepare Fixtures Models

## 1.0.0 (4. May 2017)

+ First stable release.
