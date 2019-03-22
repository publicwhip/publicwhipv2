# Milestones

See completed releases in the [ChangeLog](ChangeLog.md).

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

    So no need to rely upon [Mapit](https://mapit.mysociety.org) - so slightly faster lookups, fewer third party
    dependencies, lower cost and ensure we only rely on open source data.
    
* 1.4 - Add search system. Estimate 2 days.
    Allowing `all`, `Current MPs`, `All MPs`, `Division names`, `Constituency`, `Postcode` and `Motion text`.  

* 1.5 - Add new import/export system. Estimate 1 week.
* 2.0 (?) - Remove v1 of the website.

* 2.1 - Move away from the [parliamentary parser](http://parser.theyworkforyou.com/)
       So we can retrieve division information faster (as per [CommonsVotes](https://commonsvotes.digiminster.com/))
       
* 2.5 - Get really high code quality and unit tests (see
[PHP Code Quality Tools](https://web-techno.net/code-quality-check-tools-php/) for additional checks)
 
 * 3.0 - Have separate repositories for the 'web front end' and the backend: separate the code out appropriately
 so we become an API first platform.