# LUYA Test Suite UPGRADE

This document will help you upgrading from a LUYA Test Suite version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## from 1.0 to 2.0

[#39](https://github.com/luyadev/luya-testsuite/pull/39) In order to provide a wider range of PHP version we have included the `yoast/phpunit-polyfills` package. Therefore a few changes might be required to safely use version 2.0 of LUYA Test Suite:

+ Change `assertContains()` to `assertStringContainsString()` (unless you are not comparing against a string).
+ Ensure you **don't ignore platform reqs in your CI!** `--ignore-platform-reqs` will result in insalling the wrong version of the phpunit polyfill.
+ Do not use `setUp`, use the LUYA Test Suite `afterSetup()` instead.
+ Replace `expectExceptionMessageRegExp()` with `expectExceptionMessageMatches()` when needed.
+ Under certain circumstances, when testing against multiple PHP versions, its required to ignore the composer.lock file (therefore add the composer.lock to .gitignore list)
