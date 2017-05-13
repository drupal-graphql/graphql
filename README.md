# GraphQL for Drupal

[![Build Status](https://travis-ci.org/fubhy/graphql-drupal.svg?branch=8.x-3.x)](https://travis-ci.org/fubhy/graphql-drupal)

This module lets you craft and expose a [GraphQL] schema for [Drupal 8].

It is is built around https://github.com/Youshido/GraphQL. As such, it supports
the full official GraphQL specification with all its features.

You can use this module as a foundation for building your own schema through
custom code or you can use and extend the generated schema using the plugin
architecture of the contained sub-modules.

For ease of development, it includes the [GraphiQL] interface at
/graphql/explorer.

[Drupal 8]: https://www.drupal.org/8
[GraphQL]: http://graphql.org/
[GraphiQL]: https://github.com/graphql/graphiql/

## Built-in generated schema

The `modules` directory contains a set of modules that help to automatically
create a schema from Drupal data structures and components. By enabling these
sub-modules you can expose much of the Drupal data graph without writing a
single line of code.

Please refer to `modules/README.md` for more information.

## Example implementation

Check out https://github.com/fubhy/drupal-decoupled-app for a complete example
of a fully decoupled React and GraphQL application. Feel free to use that
repository as a starting point for your own decoupled application.

## Documentation

Please note that our documentation is outdated and in dire need of rewriting.
This is due to the vast amount of improvements and additional features we've
added to the module recently. As we are finishing up the 3.x version of this
module we will be re-doing the documentation and record a series of screencasts.

## Resources
 
* Documentation: https://www.gitbook.com/book/fgm/graphql-for-drupal
* Project homepage: https://www.drupal.org/project/graphql
* Contributing: https://github.com/fubhy/graphql-drupal

