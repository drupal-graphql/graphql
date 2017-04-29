# GraphQL extension modules

This is a collection of extension modules adding an automatic GraphQL schema on top of the `graphql` module.

## Too long, won't read

Enable all of them, fill your site with contents, fire up the GraphQL explorer and see what happens.

## Module overview

* `graphql_core`: The base module, building a custom schema out of annotated Drupal plugins. Exposes Drupal routing
  and context values per route. **Warning:** At the time of writing, context retrieval is just a proof of concept.
  For accessing the current node, it's better to use the `entity` field provided by `graphql_content`.
* `graphql_content`: Exposes all content entity types as GraphQL interfaces and bundles as object types. Fields
  configured for display will expose rendered strings by default. This behavior can be overridden by field formatter,
  as other modules in this directory do.
* `graphql_entity_reference`: If an entity reference field is configured to display the rendered entity, the field
  will expose the entity as GraphQL object instead of the rendered string.
* `graphql_file`: Replaces the *Plain url* file field formatter with a GraphQL object exposing more detailed file
  information.
* `graphql_image`: Overrides image fields to expose detailed image information for all configured image styles.
* `graphql_link`: Exposes link fields as url objects. Technically it's possible to recurse down into additional requests, to
   retrieve e.g. entities or context values from routed links. This does not mean it's a good idea and should be used
   with care.
* `graphql_block`: Query blocks by url and theme region. Currently the only use case is to retrieve field values for
  content blocks.
* `graphql_menu`: Expose menu trees by name as nested url objects.

## Detailed documentation

Not done yet, for questions ping *@pmelab* wherever you find him.

Looking at the tests might help too.

## Roadmap

* Context sensitive views integration
* Mutations based on form modes
  