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
