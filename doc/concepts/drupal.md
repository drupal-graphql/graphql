# GraphQL for Drupal

## The Drupal GraphQL schema

The diagram below exposes a subset of the GraphQL schema for Drupal 8 as 
provided by the module.

* The box on top contains the internal GraphQL types
* The box on left contains the core GraphQL types and fields
* The "entity system" box contains the schema elements introduced for the Drupal 
  entity system as a whole
* The "User entity" box contains the schema elements introduced by the Drupal user
  entity. It is typical of what is available for any single-bundle entity
* The "Relay" box contains the additional schema element introduced to support
  [Relay] integration

![A (reduced) map of the Drupal GraphQL schema](concepts.svg)

[Relay]: https://facebook.github.io/relay/
