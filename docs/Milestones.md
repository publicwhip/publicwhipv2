# Milestones

[Jump to Completed Milestones](#completed).

## Planned Milestones (in planning order)

* 0.4.5 - Create basic division editing system (with no authentication) : Estimate 3-4 days

* 0.5 - Show/List policies (aka "dreams" in v1 database). Estimate 1 day

* 0.5.5 - Add ability to modify policies. Estimate 2 days.

* 0.6 - Add authentication. Estimate 2-3 days.

    Add authentication layer supporting at least existing user logins and perhaps
    also social network logins (LinkedIn, GitHub, Google and Twitter as the primaries)
    probably powered by [HybridAuth](https://hybridauth.github.io).

* 0.7 - Add basic votes display on divisions. Estimate 2-3 days.
* 0.8 - Replace existing configuration system to a simpler one using `.env` files. Estimate 1 day
* 0.9 - Ensure templates, routing and dependency injector are cached on production. Estimate 1 day
* 0.10 - Get a better web site design in place. Estimate 4 days.
* 0.11 - Ensure adequate logging/instrumentation is in place. Estimate 1 day
* 0.21 - Get unit test coverage to at least 20% with those tests then being fully mutation covered.
        Estimate 3-4 days.
        
* 0.50 - Push to a standalone staging site for public testing. Estimate 1 day

     Emphasis that data is/will be outdated (as we will run from a cached separate copy of the database)

* 0.51 - Smoke test/bug fixes from public testing. Estimate 3-4 days
* 0.52 - Get unit test coverage to at least 60% with those tests then being fully mutation covered. Estimate 3-4 days.
* 0.70 - Integrate v1 website into code so both code basis can operate concurrently on the same server. Estimate 2 days.

    Don't promote the links to the 'new pages', but have an explanation if people come across them.
* 0.90 - Get unit test coverage to at least 95% with those tests being fully mutation covered. Estimate 3-4 days.     
* 1.0 (?) - Push to existing website and smoke test.
* 1.1 - Add constituency displays. Estimate 2 days.
* 1.2 - Add MPs display. Estimate 2 days
* 1.3 - Add local postcode lookups. Estimate 2 days.

    So no need to rely upon [Mapit](mapit.mysociety.org) - so slightly faster lookups, fewer third party
    dependencies, lower cost and ensure we only rely on open source data.
    
* 1.4 - Add search system. Estimate 2 days.
    Allowing `all`, `Current MPs`, `All MPs`, `Division names`, `Constituency`, `Postcode` and `Motion text`.  

* 1.5 - Add new import/export system. Estimate 1 week.
* 2.0 (?) - Remove v1 of the website.

* 2.1 - Move away from the [parliamentary parser](http://parser.theyworkforyou.com/)
       So we can retrieve division information faster (as per [CommonsVotes](https://commonsvotes.digiminster.com/))
 
## <a name="completed">Completed Milestones</a> (in recently completed order)

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