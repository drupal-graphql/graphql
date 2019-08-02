# Querying routes

A very powerful feature of Drupal is the ability to manage paths in entities and using path aliases to manage clean URL's. We can leverage data producers provided by the GraphQL module to query these paths from Drupal.

In this section we will look at how we can load a node from it's URL.

## Add the schema declaration

The first step, as seen in the introduction, is to add the types and fields in the schema. We can do this directly in the schema string in your own schema implementation (`src/Plugin/GraphQL/Schema/SdlSchemaMyDrupalGql.php`).

```
...

type Query {
    route(path: String!): NodeInterface
}

type Article implements NodeInterface {
    id: Int!
    uid: String
    title: String!
}

interface NodeInterface {
    id: Int!
}

...

```

In this schema we can see that we can query a node by its route, that takes a parameter called "path". We also (similar to the test schema that comes with the module) have a `NodeInterface` and an "Article" type that implements that interface. That means our "Article" will be available inside a fragment in the `NodeInterface` type.

## Adding resolvers

To add the resolvers we go to our schema implementation and call the appropriate data producers inside the `getResolverRegistry` method.

```php
use GraphQL\Error\Error;
...

/**
 * {@inheritdoc}
 */
protected function getResolverRegistry() {
  ...

  // Tell GraphQL how to resolve types of a common interface.
  $registry->addTypeResolver('NodeInterface', function ($value) {
    if ($value instanceof NodeInterface) {
      switch ($value->bundle()) {
        case 'article': return 'Article';
        case 'page': return 'Page';
      }
    }
    throw new Error('Could not resolve content type.');
  });

  $registry->addFieldResolver('Query', 'route', $builder->compose(
    $builder->produce('route_load')
      ->map('path', $builder->fromArgument('path')),
    $builder->produce('route_entity')
      ->map('url', $builder->fromParent())
  ));

  ...

  return $registry;
}
```

Here we take advantage of the `compose` method inside our resolver builder object that allows chaining multiple producers together. This technique can be very helpful when dealing with more complex scenarios.

In this example our query could look like this :

```graphql
query {
  route(path: "/node/1") {
    ... on Article {
      id
      title
    }
  }
}
```

and the response :

```json
{
  "data": {
    "route": {
      "id": 1,
      "title": "Hello GraphQL"
    }
  }
}
```
