# Change log.

These are items that have been done and released. To see what's planned, have a look at the
[Milestones](Milestones.md).

* 0.4.4 - Changes to divisions. - DONE - 2019-03-21
    
    Divisions have been split up into three different segments to allow for easier editing.
    
    
* 0.4.3 - Move over to the Doctrine coding standard. DONE - 2019-03-19

    Add more code quality checks.
    
* 0.4.2 - Ensure division data from v1 is processed correctly: Estimate 1 day. DONE - 2019-03-17
     
    Added code from `originalPublicWhipCode` and associated data extractors (in `v1migrationUtils`)
    to allow for comparisons of wiki stored division data.
    
    Added unit tests to check those extractions. Fix problem with code coverage export.
    
    More coding style improvements and fixes.
    
    Standardise on the spelling/format of `PublicWhip`.
    
* 0.4.1 - Improved coding style and fixes. DONE - 2019-03-16

* 0.4 - Get accurate information about Divisions showing: Estimate 1-2 days. DONE - 2019-03-15

    Division motion text is stored in a custom 'wiki' format which needs to be parsed.
    
* 0.3.1 - Small fixes. DONE - 2019-03-14
    
    Added continuous integration settings, missing database tables (for policies), added planned
    milestones, fix indentation in composer.json.
    
* 0.3 - Prepare for upload to Github. Estimate 1 day. DONE - 2019-03-12

    The system should present a basic styled POC (proof-of-concept) website with
    information about the project, there should be an appropriate [README](../README.md) for all
    major directories with any major changes to existing directories noted (and all
    READMEs should be able to be accessed from the website), all QA checks should pass,
    there should be an appropriate [license file](../LICENSE.txt),
    [code of conduct](CODE_OF_CONDUCT.md), [contact details](Contact.md)
    (including a [Slack channel](https://publicwhip.slack.com/)) and things should just work.
    
    Use [Parsedown](https://github.com/erusev/parsedown) for markdown formatting.
    
    Use [Pure](https://purecss.io/) for simple layout.
    
* 0.2 - Basic rendering with existing database. DONE - 2019-03-11
    
    Using the base project, we should then have some basic pages rendered and some basic Divisions
    information showing.
    
* 0.1 - Base/skeleton project. DONE - 2019-03-10

    Setup a base/skeleton project which can be used by anyone.
    
    Using:
    - [PHP](https://php.net) 7.2 for the main language,
    - [Slim Framework](https://www.slimframework.com) as the framework,
    - [PHP-DI](https://php-di.org) for dependency injection
    - [Monolog](https://github.com/Seldaek/monolog) for logging,
    - [Twig](https://twig.symfony.com) as the templating engine,
    - [PHPDebugBar](http://phpdebugbar.com/) for debugging,
    - [SwiftMailer](https://swiftmailer.symfony.com/) for email handling
    - [Laravel Database](https://laravel.com/docs/5.8/database) for database connections
    - [Composer](https://getcomposer.org) for package control
    
    To enable testing, this is paired with:
    - [Docker](http://docker.com) containers for multi-platform testing which has:
    - [Nginx](http://nginx.org) as the web server
    - [MariaDB](https://mariadb.org/) as the database server
    - [PHPMyAdmin](https://www.phpmyadmin.net/) for simple database administration
    - [MailHog](https://github.com/mailhog/MailHog) for email interception
    Along with an anonymized copy of the live database with just core tables included.
    
    To aid development, the project has the following code quality control tools setup:
    - [GrumPHP](https://github.com/phpro/grumphp) to run all the following checks on commits:
    - [PHPUnit](https://phpunit.de/) for unit testing
    - [PHP Mess Detector](https://phpmd.org) for code inspection
    - [PHP Stan](https://github.com/phpstan/phpstan) for static analysis
    - [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for code quality inspections
    - [Roave Security Advisories](https://github.com/Roave/SecurityAdvisories) to check for outdated packages
    - [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd) to reduce duplicated code
    - [Infection](https://infection.github.io/) for mutation testing
    - [PHP Parallel Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint) for basic code checks
    - [Sensiolabs Security Checker](https://github.com/sensiolabs/security-checker) to check for outdated packages
    
    Also included are [PHPStorm](https://www.jetbrains.com/phpstorm/) code style and inspection profiles.