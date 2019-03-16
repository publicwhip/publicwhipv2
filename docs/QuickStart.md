# Quick Start

If you are hoping to [Contribute](CONTRIBUTING.md) to the development of PublicWhip 2, here's a quick start guide:

## Getting the existing code

1. Ensure you have [Docker](http://docker.com) on your machine.

   It can be 'fiddly' on Windows 10 Professional from experience. It took me several reboots, disabling/re-enabling
   HyperV (which kept being turned back on or off via the `Device Security->Core Isolation` section in Windows.)

2. If you have [Git](https://git-scm.com) on your machine (ideally needed for contributing back):
       Clone the repository from Github using
       either `git clone git@github.com:publicwhip/publicwhip2.git` or
       `git clone https://github.com/publicwhip/publicwhip.git`

3. If you don't have Git, try to download the ZIP archive from Github at
    https://github.com/publicwhip/publicwhip2/archive/master.zip and then unzip it to a location of you choice.
    
4. Go into the `publicwhip2/docker` folder and type `docker-compose up` and wait a few (your console should quiet
   down once the import has completed).
   
5. You now just need to install the composer directory. I actually prefer doing this from my native OS by going
   into the project directory and just typing `composer install` (as, if I'm developing on Windows, this will
   create the necessary `vendor/bin/*.bat` files). However, it may be advisable for you to use
   Docker initially - so type (or copy and paste): `docker exec -w /data publicwhip-php sh -c "composer install"`

6. You should now be able to go to http://127.0.0.1/ and see your local test site.

## Configuring your IDE.

I personally use [PHPStorm](https://www.jetbrains.com/phpstorm/) with the
[PHP Inspections Ultimate](https://kalessil.github.io/php-inspections-ultimate.html) and
[EditorConfig](https://plugins.jetbrains.com/plugin/7294-editorconfig) plugins.

If you use PHPStorm as well, the included `.idea` folder should automatically configure everything for you.

Other IDEs will be able to make use of the `.editorconfig` file in the root folder to help.

