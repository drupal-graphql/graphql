## Metatag

The easiest way to use the Metatag module together with GraphQL is to use the [GraphQL Metatag](https://www.drupal.org/project/graphql_metatag) module. It comes with built-in support for using metatags with GraphQL.

## Metatag Queries

```
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

```
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
      ],
    }
  }
}
```

This way you can easily manipulate SEO information in the Node in Drupal but still be able to properly inject this in your application by fetching this information out of the node.

You can of course use this in any kind of query that would return the entity that holds the metatag information. As an example querying a route and getting from that entity the metatag information would look something like this : 

``` 
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

```
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

for more information on routes check out the [routes documentation](queries/routes.md). Writting custom plugins you could also mutate fields of this type. You can learn more about these in the section [Creating mutation plugins](mutations/creating-mutation-plugins.md).