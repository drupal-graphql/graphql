# Built in Data Producers

The Drupal GraphQL module comes with many built in data producers to help solve typical scenarios you might need to support in your API. In this section we will explain briefly each one of them and how it can be used.

As the module is developed more data producers can be added to the core module. Its also important to note that many useful data producers can live in "user land" as contrib modules can add support for GraphQL by adding custom data producers (e.g. a Metatag data producer that lets you resolve metatags using the [Metatag module](https://www.drupal.org/project/metatag) or a Search API integration as seen in [GraphQL Search API](https://github.com/drupal-graphql/graphql-search-api))

## List of data producers

This list includes all available data producers inside GraphQL to this day and briefly describes what they are about and how to be used.

### Context data producers

* **Context** (`context`) : Request arbitrary Drupal context objects with GraphQL. 

### Entity data producers

* **Entity access** (`entity_access`) : Returns whether the given user has entity access given an entity such as a node.
* **Entity bundle** (`entity_bundle`) : Returns the name of the entity's bundle given an entity such as a node.
* **Entity changed date** (`entity_changed`) : Returns the entity changed date given an entity such as a node.
* **Entity created date** (`entity_created`) : Returns the entity created date given an entity such as a node.
* **Entity description** (`entity_description`) : Returns the entity description given an entity.
* **Entity id** (`entity_id`) : Returns the entity identifier.
* **Entity label** (`entity_label`) : Returns the entity label given an entity such as a node.
* **Entity language** (`entity_language`) : Returns the entity language.
* **Entity load** (`entity_load`) : Loads a single entity given parameters like type, id (optional), language (optional) bundles (optional), access (TRUE by default), access_user (current user by default) or access_operation ("view" by default).
* **Entity load multiple** (`entity_load_multiple`) : Loads a multiple entities given parameters like type, ids (optional), language (optional), bundles (optional), access (TRUE by default), access_user (current user by default) or access_operation ("view" by default)
* **Entity load by uuid** (`entity_load_by_uuid`) : Loads a single entity by Uuid with access control parameters: access (TRUE by default), access_user (current user by default) or access_operation ("view" by default).
* **Entity owner** (`entity_owner`) : Returns the entity owner given an entity such as a node.
* **Entity published** (`entity_published`) : Returns whether the entity is published given an entity such as a node.
* **Entity rendered** (`entity_rendered`) : Returns the rendered entity.
* **Entity translation** (`entity_translation`) : Returns the translated entity with access control parameters: access (TRUE by default), access_user (current user by default) or access_operation ("view" by default).
* **Entity translations** (`entity_translations`) : Returns all available translations of an entity with access control parameters: access (TRUE by default), access_user (current user by default) or access_operation ("view" by default).
* **Entity type** (`entity_type_id`) : Returns an entity's entity type.
* **Entity url** (`entity_url`) : Returns the entity's url.
* **Entity uuid** (`entity_uuid`) : Returns the entity's uuid.

### Image data producers

* **Image derivative** (`image_derivative`) : Returns image derivative properties (image style url width and height) given a image/file entity and a style name.
* **Image Style URL** (`image_style_url`) : Returns the URL of an image derivative given a an image derivative.
* **Image url** (`image_url`) : Returns the url of an image entity given an image/file entity

### Entity reference data producers 

* **Entity Reference** (`entity_reference`) : Loads entities from an entity reference field given an entity and a field name.

### Menu Data producers

* **Menu Links** (`menu_links`) : Returns the menu links of a menu, given a menu entity (entity:menu).
* **Menu Link url** (`menu_link_url`) : Returns the url of a menu link given a menu link.
* **Menu Link label** (`menu_link_label`) : Returns the label of a menu link.
* **Menu Link expanded** (`menu_link_expanded`) : Returns whether a menu link is expanded.
* **Menu Link description** (`menu_link_description`) : Returns the description of a menu link.
* **Menu Link attribute** (`menu_link_attribute`) : Returns an attribute of a menu link given a menu link and an attribute name.
* **Menu tree subtree** (`menu_tree_subtree`) : Returns the subtree of a menu tree element.
* **Menu tree link** (`menu_tree_link`) : Returns the link of a menu tree element.

### Routing

* **Route load** (`route_load`) : Loads a route given a path, returning a `\Drupal\Core\Url`
* **Route entity** (`route_entity`) : The entity belonging to the current url, given a url and a language (optional).
* **URL Path** (`url_path`) : The processed url path. Given a `\Drupal\Core\Url` returns the string value for the path.

### Typed data

* **Property path** (`property_path`) : Resolves a typed data value at a given property path. Takes as arguments a path, value and type (optional).

## How to use the producers

This is no doubt a very long list of supported properties by itself, but just as it is it provides no real value. The real value is in how these data producers can be combined to perform complex queries and mutations. 

We will look how to use the data producers together in the section "Queries" where we will do many common query scenarios like querying nodes or taxonomies, menus, custom fields, entity reference fields etc..
