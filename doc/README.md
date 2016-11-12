# GraphQL for Drupal

This module generates and exposes a [GraphQL] schema for [Drupal 8] entities, and 
allows you to expose your own custom schema in a consistent way and with minimal
effort.

It is probably the easiest way to build headless Drupal sites using the popular 
[React] / [Relay] couple for the front-end, and on top of the traditional fast 
Drupal site building for the content modeling and management.

[Drupal 8]: https://www.drupal.org/8
[GraphQL]: http://graphql.org/
[React]: https://facebook.github.io/react/
[Relay]: https://facebook.github.io/relay/


## Features

### Built-in schema

By default, the module exposes all content and configuration entities as a 
Relay-compliant schema making the whole Drupal entity reference graph model 
available to clients: entities, ids and references.
 
It provides a fully data-based process, which does not trigger the theme system, 
and includes full cacheability metadata for low overhead.  


### Developer experience 

The module is meant as a basis for custom development rather than pure site 
building. As such, at this point it only exposes entity identifiers and labels, 
leaving it up to you as a developer to choose whether/how to expose fields and 
non-entity data. To help you with this task, it provides base objects you only 
need to extend to define your own schema.

For ease of development, it includes the [GraphiQL] in-browser IDE, already
configured for Drupal in authenticated mode.

[GraphiQL]: https://github.com/graphql/graphiql


## Resources
 
* Documentation: https://www.gitbook.com/book/fgm/graphql-for-drupal
* Project homepage: https://www.drupal.org/project/graphql
* Contributing: https://github.com/fubhy/graphql-drupal

