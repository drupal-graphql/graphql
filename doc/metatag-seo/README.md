# Metatags

## Metatag

The easiest way to use the Metatag module together with GraphQL is to use the [GraphQL Metatag](https://www.drupal.org/project/graphql_metatag) module. It comes with built-in support for using metatags with GraphQL.

## Metatag Queries

```graphql
{
  nodeById(id: "1") {
    entityId
    ... on NodeArticle {
      entityId
      entityMetatags {
        key
        value
      }
    }
  }
}
```

This will give you something like this as a response :

```json
{
  "data": {
    "nodeById": {
      "entityId": "1",
      "entityMetatags": [
        {
          "key": "title",
          "value": "Article name | My drupal site"
        },
        {
          "key": "canonical",
          "value": "https://websiteurl.com/node/1"
        }
      ]
    }
  }
}
```

This way you can easily manipulate SEO information in the Node in Drupal but still be able to properly inject this in your application by fetching this information out of the node.

You can of course use this in any kind of query that would return the entity that holds the metatag information. As an example querying a route and getting from that entity the metatag information would look something like this :

```graphql
{
  route(path: "/article-name") {
    ... on EntityCanonicalUrl {
      entity {
        entityLabel
        entityMetatags {
          key
          value
        }
      }
    }
  }
}
```

This would return any information on this particular route, including the meta information requested.

```json
{
  "data": {
    "route": {
      "entity": {
        "entityLabel": "Article name",
        "entityMetatags": [
          {
            "key": "title",
            "value": "Article name | My drupal site"
          },
          {
            "key": "canonical",
            "value": "https://websiteurl.com/article-name"
          }
        ]
      }
    }
  }
}
```

for more information on routes check out the [routes documentation](https://github.com/drupal-graphql/graphql/tree/3c8b237bc3698c82b05291d528fb6701e8d7b501/doc/metatag/queries/routes.md). Writting custom plugins you could also mutate fields of this type. You can learn more about these in the section [Creating mutation plugins](https://github.com/drupal-graphql/graphql/tree/3c8b237bc3698c82b05291d528fb6701e8d7b501/doc/metatag/mutations/creating-mutation-plugins.md).

### Known issues

There is a currently an [issue](https://github.com/drupal-graphql/graphql/issues/609) opened for using the metatag module together with the GraphQL module :

_"If a module \(e.g. metatag\) introduces a new primitive data type, it is not part of the derived types, but any field using it will reference it. That results in a "Missing type metatag." exception."_

So for now you need to include a custom Scalar as a workaround to avoid errors in GraphQL due to this missing type. Create a file inside a custom module of your own, named for example "MetatagScalar.php" where a custom scalar will be defined. In this example the module's name is graphql_custom as seen from the namespace bellow. Make sure to not conflict with existing namespaces when defining it.

```php
<?php

namespace Drupal\graphql_custom\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\Scalars\Internal\StringScalar;

/**
 * Metatag module dummy type.
 *
 * Metatag module defines a custom data type that essentially is a string, but
 * not called string. And the GraphQL type system chokes on that.
 *
 * @GraphQLScalar(
 *   id = "metatag",
 *   name = "metatag",
 *   type = "string"
 * )
 */
class MetatagScalar extends StringScalar {

}
```
