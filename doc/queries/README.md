# Queries

Queries in Drupal GraphQL work by combining data producers in order to resolve values of fields added to the schema. As we have already seen in previous examples its in most cases a 2-step process of first adding the schema information and then resolving whatever we added in the schema to actual data.

In this section we will learn how to use the built-in data producers to resolve common data required by most websites or application that will use GraphQL. The code with all the demo queries and mutations in these docs can be found in [this repository](https://github.com/joaogarin/mydrupalgql).

Lets quickly look again at a simple field on an existing entity like the "Article" content type that comes with Drupal. First we make that field available in the schema, lets take as an example the title : 

```
type Article implements NodeInterface {
    ...
    title: String!
    ...
}
```

We now need to resolve the title field via data producer provided by the module `entity_label` which will give us the label of an entity : 

```php 
    $registry->addFieldResolver('Article', 'title',
      $builder->produce('entity_label', [
        'mapping' => [
          'entity' => $builder->fromParent(),
        ],
      ])
    );
```

We now can run a query like this : 

```graphql
article(id:1) {
    title
}
```

and get something like : 

```json
{
  "data": {
    "article": {
      "title": "Hello GraphQL"
    }
  }
}
```

Next we will look at more complex scenarios like loading custom fields, menus and menu links, taxonomies and other kind of fields.
