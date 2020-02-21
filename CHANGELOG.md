# LUYA TEST SUITE

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.0.27 (21. February 2019)

+ Allow any version of LUYA dependencies.

## 1.0.26 (30. December 2019)

+ Ensure the generatefixture data works with composite keys and escapes table data.

## 1.0.25 (25. December 2019)

+ [#31](https://github.com/luyadev/luya-testsuite/issues/31) Added new `generatefixture` console command which will be auto boostraped in order to generate fixtures based on existing models or table names.
+ Added more ngrest test case default api controller action tests.

## 1.0.24 (5. December 2019)

+ Added new dummy session component.
+ Added travis php 7.4 support
+ Improve server test case debug message

## 1.0.23 (22. October 2019)

+ [#29](https://github.com/luyadev/luya-testsuite/pull/29) Add new getTableName() method to make ActiveRecord fixture usable without $modelClass property.

## 1.0.22 (3. October 2019)

+ Added new admin database table trait methods.
+ Ensure fixture data can be empty on various methods.

## 1.0.21 (17. September 2019)

+ Added new PageScope test system for CMS Pages.

## 1.0.20.2 (13. September 2019)

+ Added missing `admin_auth` table `pool` field in `AdminDatabaseTableTrait`.

## 1.0.20.1 (8. September 2019)

+ Added missing user fixture fields for `createUserFixture` in `AdminDatabaseTableTrait`.

## 1.0.20 (8. September 2019)

+ [#23](https://github.com/luyadev/luya-testsuite/issues/23) Move permission into traits add new PermissionScope object to make api tests easy.

## 1.0.19 (4. September 2019)

+ [#22](https://github.com/luyadev/luya-testsuite/pull/22) Added trait for testing console output easier.

## 1.0.18 (9. August 2019)

+ [#20](https://github.com/luyadev/luya-testsuite/issues/20) Added NgRestTestCase table support for admin 2.0
+ [#21](https://github.com/luyadev/luya-testsuite/issues/21) Added option to run an action with auth headers `runControllerAction()`.

## 1.0.17.2 (26. June 2019)

+ Added new CMS version 2.0 constraint.

## 1.0.17.1 (16. June 2019)

+ Fix LUYA admin dependency version constraint.

## 1.0.17 (27. May 2019)

+ Update version constraint to allow luya admin version 2.0

## 1.0.16 (16. April 2019)

+ Added new skipIfExists property, now by default the table will only created if its not already existing.

## 1.0.15 (16. April 2019)

+ Fixed bug in cleanup() and added rebuild() option to first cleanup and then recreate tables.

## 1.0.14 (1. April 2019)

+ [#19](https://github.com/luyadev/luya-testsuite/pull/19) Added permissions control to NgRestTestCase, fixed basic api tests.

## 1.0.13.3 (17. November 2018)

+ Update version constraint for CURL library.

## 1.0.13.2 (25. October 2018)

+ [#17](https://github.com/luyadev/luya-testsuite/issues/17) Fixed issue with callable and not scalar rule defintions.

## 1.0.13.1 (24. October 2018)

+ [#16](https://github.com/luyadev/luya-testsuite/issues/16) Fixed a problem introduced in [#14](https://github.com/luyadev/luya-testsuite/issues/14) which breaks compatibility with safe attributes. Add option to force this behavior.

## 1.0.13 (15. October 2018)

+ [#15](https://github.com/luyadev/luya-testsuite/issues/15) Add travis CI and code climate coverage integrations.
+ [#14](https://github.com/luyadev/luya-testsuite/issues/14) Remove safe validators from column creation.
+ [#13](https://github.com/luyadev/luya-testsuite/issues/13) Convert integer and boolean rule types into integer and boolean column types.

## 1.0.12 (8. October 2018)

+ [#12](https://github.com/luyadev/luya-testsuite/pull/12) Fixed issue with NgRestTestCase - Added missing is_api_user to User schema fixture

## 1.0.11 (18. July 2018)

+ [#8](https://github.com/luyadev/luya-testsuite/issues/8) Added new CmsBlockGroupTestCase

## 1.0.10 (9. May 2018)

+ [#7](https://github.com/luyadev/luya-testsuite/issues/7) New ActiveRecordFixture with auto build of table schemas.
+ [#6](https://github.com/luyadev/luya-testsuite/issues/6) NgRest TestCase for Model, API and Controller.

## 1.0.9 (20. April 2018) and 1.1.0 (26. March 2018)

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
