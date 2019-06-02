# Querying routes

Another query type provided by default by the Drupal GraphQL module is the **route**. Querying for routes is simple, you provide a path as an argument and the query will return a Url which contains fields for every defined context in Drupal. This way it’s possible to pull the content language, current user or node for a given path. Any context provided by a contrib module will also be picked up automatically.

## Performing a basic route query

```graphql
query {
  route(path: "/node/1") {
    alias
  }
}
```

This will return information about the path we just provided :

```javascript
{
  "data": {
    "route": {
      "alias": "/my-node-path-alias"
    }
  }
}
```

## Getting context information from a route query

But with a route query like we mentioned you can do much more, and fetch context associated with the route. Lets see how we can get information about the node associated with a route or language associated with a route.

```graphql
{
  route(path: "/my-node-query-alias") {
    ... on EntityCanonicalUrl {
      nodeContext {
        entityBundle
      }
      languageInterfaceContext {
        id
        name
      }
    }
  }
}
```

this query will return :

```javascript
{
  "data": {
    "route": {
      "nodeContext": {
        "entityBundle": "article"
      },
      "languageInterfaceContext": {
        "id": "en",
        "name": "English"
      }
    }
  }
}
```

This is a very powerfull way of getting related information with the route you are querying. Get over to GraphiQL and start experimenting with route queries, you can get information on alias, language, user, node entity etc..

