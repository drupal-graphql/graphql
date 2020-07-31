# Data Producers

The 4.x version of the Drupal GraphQL module is built on top of a concept called "Data Producers". These help making data available to a schema by using in part some built-in producers that come with the module but you can also make your own custom producers to make more complex data structures available.

A data producer is more or less a function that takes arguments (either from an end user query or static) and resolves these into some other data, for example taking an entity (such as a node) and giving back a particular field.

For example, if we want to make a custom field available for a schema let's imagine we have a `creator` field in the "Article" content type. For this field we can have a producer that takes an entity and returns or resolves the creator field. Let's apply this to our custom schema which alreay defines an "Article" type.

## Add the field to the schema

```
type Article implements NodeInterface {
    id: Int!
    uid: String
    title: String!
    render: String
    creator: String
}
```

We are telling the schema that we have a new field on the "Article" type called creator. This is how a user will consume the API so we are not yet telling how to get that data. For now this would return NULL as we don't yet have a resolver in place.

## Add the resolver

The following code is an example of how a data producer for a creator field on the Article type can be implemented in code.

```php
$registry->addFieldResolver('Article', 'creator',
  $builder->produce('property_path')
    ->map('type', $builder->fromValue('entity:node'))
    ->map('value', $builder->fromParent())
    ->map('path', $builder->fromValue('field_article_creator.value'))
);
```

Essentially this is what you need to do every time you want to make a field available in the schema. We tell Drupal where and how to get the data and specify where this maps to.

This particular resolver uses the `property_path` data producer that comes with the GraphQL module. It's one of the most common ones and you will find yourself using it often to resolve any kind of property on an entity. The module includes a lot more which we will see in the "Built in Data Producers" section.

## Notes

You can find a list of all Data Producers provided by the module inside `src/Plugin/GraphQL/DataProducer` folder.

The 4.x module leverages the advantages of custom schemas and data producers, where you can create your own API structure. In the 3.x module if you had a field named `field_article_creator` the API would expose this field as `fieldArticleCreator`. This means that the API consumer needs to have knowledge about how Drupal structures its data internally.
In the 4.x module you can (and also need to) define your own custom schemas (and data producers) and therefore create your own structure so that someone that uses the API does not need to know how Drupal structures the data.
