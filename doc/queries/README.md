# Queries

Queries in the graphql module work by combining data producers in order to resolve values of fields that are added to the schema. As we have already seen in previous examples, it's in most cases a 2-step process of first adding the schema information and then resolving whatever we added in the schema to actual data.

In this section we will learn how to use the built-in data producers to resolve common data as it is required by most web application that use GraphQL. The code with all the demo queries and mutations in these docs can be found in [this repository](https://github.com/joaogarin/mydrupalgql).

Let's take a look at a simple field on an existing entity like the "Article" content type that comes with Drupal. As a first step, we include this field in the schema. Let's take the title as an example:

```
type Article implements NodeInterface {
    ...
    title: String!
    ...
}
```

We now need to resolve the title field via a data producer which is already provided by the `entity_label` module. This will give us the label of an entity:

```php
$registry->addFieldResolver('Article', 'title',
  $builder->produce('entity_label')
    ->map('entity' => $builder->fromParent())
);
```

We can now run a query like this:

```graphql
query {
  article(id:1) {
      title
  }
}
```

and get the following result:

```json
{
  "data": {
    "article": {
      "title": "Hello GraphQL"
    }
  }
}
```

Next, we will look at more complex scenarios like loading custom fields, menus, menu links, taxonomies and other kind of fields.
