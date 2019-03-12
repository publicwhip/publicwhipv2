# How to Contribute

At the moment, all code in this repository should be 'database compatible' with PublicWhip version 1.

This means that any changes `PW 1` makes to the database, this system should cope with. Any data written to
existing tables by `PW 2` must be able to be read/interpreted by `PW 1` unless it is an acceptable 'outlier' -
for example, password storage will probably not be backwards compatible but as long as `PW 2` users know to
login via `PW 2` then that will be fine. It would not be acceptable for divisions to be unreadable by either
system however.
## Pull Requests

1. Fork the `PublicWhipV2` repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch against the version branch for which your fix is intended.

It is very important to separate new features or improvements into separate feature branches, and to send a
pull request for each branch. This allows each feature or improvement to be reviewed and merged individually.

## Style Guide

All pull requests must adhere to the
[PSR-12 standard](https://github.com/php-fig/fig-standards/blob/master/proposed/extended-coding-style-guide.md).

No `grumPHP` tests should fail: you can check this at any time by using `\vendor\bin\grumphp run`
(or on Windows: `vendor\bin\grumphp.bat run`);

Yoda conditions should be used. Along with the PHP Code Sniffer configuration in `qaTools`, there is also a
PHPStorm coding style and inspections configuration file.

## Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage. PublicWhip uses
[PHPUnit](https://phpunit.de) for testing.



