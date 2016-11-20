# Executing the automated tests

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module comes with PHPUnit tests. You need a working Drupal 8 installation
and a checkout of the GraphQL module in the modules folder.

    cd /path/to/drupal-8/core
    ../vendor/bin/phpunit ../modules/graphql/tests/src/Unit
    ../vendor/bin/phpunit ../modules/graphql/tests/src/Integration

You can also execute the test cases from the web interface at ``/admin/config/development/testing``.
