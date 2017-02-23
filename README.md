# GraphQL for Drupal

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module aims to generate and expose a [GraphQL] schema for [Drupal 8]
entities. Currently, you can expose your own custom schema through custom code
with minimal effort.

[Drupal 8]: https://www.drupal.org/8
[GraphQL]: http://graphql.org/

The module is currently meant as a basis for custom development rather than pure
site building. As such, at this point it only exposes entity identifiers and labels, 
leaving it up to you as a developer to choose whether/how to expose fields and 
non-entity data. To help you with this task, it provides base objects you only 
need to extend to define your own schema.

For ease of development, it includes the [GraphiQL] in-browser IDE.

[GraphiQL]: https://github.com/graphql/graphiql

## Future features

### Built-in schema

By default, the module is going to expose all content and configuration entities as a 
GraphQL schema making the whole Drupal entity graph available to clients.

## Resources
 
* Documentation: https://www.gitbook.com/book/fgm/graphql-for-drupal
* Project homepage: https://www.drupal.org/project/graphql
* Contributing: https://github.com/fubhy/graphql-drupal

