# GraphQL for Drupal

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module lets you craft and expose a [GraphQL] schema for [Drupal 8].

Currently, you can expose your own custom schema through custom code with
minimal effort. In the near future, we will add the ability to automatically
generate a full schema from the underlying Drupal data graph.

The module is currently meant as a basis for custom development rather than pure
site building. As such, it leaves it up to you as a developer to choose
whether/how to expose fields and non-entity data. To help you with this task,
it provides base a flexible integrations layer for you to define your own
schema.

For ease of development, it includes the [GraphiQL] interface at
/graphql/explorer.

This module is built around https://github.com/Youshido/GraphQL. As such, it
supports the full official GraphQL specification with all its features.

[Drupal 8]: https://www.drupal.org/8
[GraphQL]: http://graphql.org/
[GraphiQL]: https://github.com/graphql/graphiql/

## Examples

The module itself contains an submodule which serves as an example for how to
build a custom schema.

Check out https://github.com/fubhy/drupal-decoupled-app for a complete example
of a fully decoupled React and GraphQL application. Feel free to use that
repository as a starting point for your own decoupled application.

## Built-in generated schema

The `modules` directory contains a set of modules that help to automatically
create a GraphQL schema from Drupal data structures and components. Please
refer to `modules/README.md` for more information.

## Resources
 
* Documentation: https://www.gitbook.com/book/fgm/graphql-for-drupal
* Project homepage: https://www.drupal.org/project/graphql
* Contributing: https://github.com/fubhy/graphql-drupal

