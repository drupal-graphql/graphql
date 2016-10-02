# GraphQL integration for Drupal 8

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module generates and exposes a GraphQL schema for all content entity types.

Project homepage: https://www.drupal.org/project/graphql

## Installation

In order to install this module, since we are currently using a fork of the library that this module depends on, you
have to specify a custom repository in your composer.json.

```
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "youshido/graphql",
        "version": "dev-drupal",
        "source": {
          "url": "https://github.com/fubhy/GraphQL.git",
          "type": "git",
          "reference": "drupal"
        }
      }
    }
  ]
```

After adding this to your composer.json, you can run "composer require drupal/graphql:8.x-3.x".

## Contributing

For some time, development will happen on GitHub using the pull request model:
in case you are not familiar with that, please take a few minutes to read the
[GitHub article](https://help.github.com/articles/using-pull-requests) on using
pull requests.

There are a few conventions that should be followed when contributing:

* Always create an issue in the [drupal.org GraphQL issue queue](https://www.drupal.org/project/issues/graphql)
  for every pull request you are working on.
* Always cross-reference the Issue in the Pull Request and the Pull Request in
  the issue.
* Always create a new branch for every pull request: its name should contain a
  brief summary of the ticket and its issue id, e.g **readme-2276369**.
* Try to keep the history of your pull request as clean as possible by squashing
  your commits: you can look at the [Symfony documentation](http://symfony.com/doc/current/cmf/contributing/commits.html)
  or at the [Git book](http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages)
  for more information on how to do that.

## Executing the automated tests

This module comes with PHPUnit tests. You need a working Drupal 8 installation
and a checkout of the GraphQL module in the modules folder.

    cd /path/to/drupal-8/core
    ../vendor/bin/phpunit ../modules/graphql/tests/src/Unit
    ../vendor/bin/phpunit ../modules/graphql/tests/src/Integration

You can also execute the test cases from the web interface at ``/admin/config/development/testing``.
