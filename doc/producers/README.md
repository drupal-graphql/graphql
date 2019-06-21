# Data Producers

The 4.x version of the Drupal GraphQL module is built on top of a concept of "Data producers". These help making data available to a schema by using in part some built-in producers that come with the module but also you can make your own more complex producers to make more complex data structures available.

A data producer is more or less a function that takes some arguments or parameters (either from the end user query or static) and resolves that into some other data, like taking an entity (such as a node) and giving back a particular field.

So if we think about making a custom field available for a schema lets imagine we have a `creator` field in the "Article" content type we could have a producer that takes an entity and resolves the creator field. Lets go though what we would need to do to the schema we created previously (which already has an "Article" type available).

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

We are telling the schema we have a new field on "Article" called creator, this is how a user will consume the API so we are not yet telling how to get that data, for now this will always return NULL as we don't have a resolver for it just yet.

## Add the resolver

For some more complex scenario (and provide some insight on one of the usefulness of the 4.x module) lets imagine our field has a name like `field_article_creator`. In the 3.x version of the module we would have an API that matches this name so someone would call it like `fieldArticleCreator`, this is a little verbose and simply calling it with `creator` seems a little cleaner and more logical. 

```php 
    $registry->addFieldResolver('Article', 'creator',
      $builder->produce('property_path', [
        'mapping' => [
          'type' => $builder->fromValue('entity:node'),
          'value' => $builder->fromParent(),
          'path' => $builder->fromValue('field_article_creator.value'),
        ],
      ])
    );
```

Essentially this is what you need to do every time you have to make a field available in the schema. We tell Drupal where and how to get that data and specify also where this maps to.

This particular resolver uses the `property_path` Data producer that comes with the GraphQL module. Its not by chance here in this example we am using this particular data producer, its one of the most common ones and one you will find yourself using quite often to resolve any kind of property on an entity. The module includes a lot more which we will look in the "Built in Data producers" section.

## Notes

You can find a list of all Data Producers provided by the module inside `src/Plugin/GraphQL/DataProducer` folder.
