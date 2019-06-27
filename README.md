# Introduction

[![Build Status](https://img.shields.io/travis/drupal-graphql/graphql.svg)](https://travis-ci.org/drupal-graphql/graphql) [![Code Coverage](https://img.shields.io/codecov/c/github/drupal-graphql/graphql.svg)](https://codecov.io/gh/drupal-graphql/graphql) [![Code Quality](https://img.shields.io/scrutinizer/g/drupal-graphql/graphql.svg)](https://scrutinizer-ci.com/g/drupal-graphql/graphql/?branch=8.x-4.x)

## GraphQL for Drupal

This module lets you craft and expose a [GraphQL](http://graphql.org/) schema for [Drupal 8](https://www.drupal.org/8).

It is is built around [https://github.com/webonyx/graphql-php](https://github.com/webonyx/graphql-php). As such, it supports the full official GraphQL specification with all its features.

You can use this module as a foundation for building your own schema through custom code or you can use and extend the generated schema using the plugin architecture and the provided plugin implementations form the sub-module.

For ease of development, it includes the [GraphiQL](https://github.com/graphql/graphiql/) interface at`/graphql/explorer`.

### Installation

This module requires composer for installation. To install, simply run `composer require drupal/graphql`.

### Documentation

[Documentation](https://drupal-graphql.gitbook.io/graphql/v/8.x-4.x/ is hosted on [gitbook.io](http://www.gitbook.io).

### Resources

* Project homepage: [https://www.drupal.org/project/graphql](https://www.drupal.org/project/graphql)
* Contributing: [https://github.com/drupal-graphql/graphql](https://github.com/drupal-graphql/graphql)
