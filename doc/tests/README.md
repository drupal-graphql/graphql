# Executing the automated tests

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module comes with PHPUnit Kernel and Functional tests. You need a working Drupal 8 installation
and a checkout of the GraphQL module in the modules folder.

Instructions below assume a site running 

* on a Linux server
* from `/var/www/d8`,
* at `http://localhost/`
* using a database available at `mysql://some_user:some_pass@localhost/some_db`. 

Adjust configuration according to your actual setup.


## Run the tests from the Simpletest UI

* Enable simpletest

      cd /var/www/d8/core
      ../vendor/bin/drush en -y simpletest
* Navigate to `http://localhost/admin/config/development/testing`
* The tests appear in the `GraphQL` section


## Run the tests from the CLI or PhpStorm
### Setup Drupal for CLI tests with PHPunit

Basically follow the instructions on https://www.drupal.org/docs/8/phpunit/running-phpunit-tests#non-unit-tests


* Create a custom PHPunit run configuration from the default core version:

      $ cd /var/www/d8/core
      $ cp phpunit.xml.dist phpunit.xml
* Edit the `phpunit.xml` file    
  * (Optional) In order to get HTML output from Functional tests, uncomment the 
    `printerClass` attribute, so that the `<phpunit>` element looks like:

      ```
      <phpunit bootstrap="tests/bootstrap.php" colors="true"
               beStrictAboutTestsThatDoNotTestAnything="true"
               beStrictAboutOutputDuringTests="true"
               beStrictAboutChangesToGlobalState="true"
               checkForUnintentionallyCoveredCode="false"
               printerClass="\Drupal\Tests\Listeners\HtmlOutputPrinter">
      ```
  * In order for functional tests to have access to a database, to the site, and 
    output results as files, configure the `SIMPLETEST_BASE_URL`,
    `SIMPLETEST_DB` and `BROWSERTEST_OUTPUT_DIRECTORY` variables, so that the 
    `<php>` element looks like:
    
      ```
      <php>
        <ini name="error_reporting" value="32767"/>
        <ini name="memory_limit" value="-1"/>
        <env name="SIMPLETEST_BASE_URL" value="http://localhost/" />
        <env name="SIMPLETEST_DB" value="mysql://some_user:some_pass@localhost/some_db" />
        <env name="BROWSERTEST_OUTPUT_DIRECTORY" value="/var/www/d8/sites/simpletest/browser_output" />
      </php>
      ```
  * Ensure the `BROWSERTEST_OUTPUT_DIRECTORY` exists and is writable by the web 
    server.
    
       ```
       mkdir -p /var/www/d8/sites/simpletest/browser_output
       chmod 777 /var/www/d8/sites/simpletest/browser_output
       ```

### Run the tests from the CLI

    cd /var/www/d8/core
    # Cleanup previous test runs
    rm -fr ../sites/simpletest/browser_output/*
    
    # Run tests
    ../vendor/bin/phpunit ../modules/graphql/tests/src


### Run the tests from PhpStorm

* Create a PhpStorm "run configuration" based on the `PhpUnit` template.
  * "Single instance only", to avoid leaving tests unterminated when debugging 
  * "Scope": directory `/var/www/d8/modules/contrib/graphql/tests/src`
  * "Use alternative configuration file": `/var/www/d8/core/phpunit.xml`
  * "Test runner options" (optional): `--debug`
  * "Custom working directory": `/var/www/d8/core`
* The PhpUnit run window will throw an error like 
  `Test framework quit unexpectedly` even with all tests passing: you can ignore
  it.
