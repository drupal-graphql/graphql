# Queries

Graphql is a query language and so the first thing we will be goign through is how can you start making queries to Drupal. One of the great benefits of GraphQL is how intuitive the query syntax and corresponsing responses look like. Essencially the query is a lot like how you want the response to look like but without the values. Lets have a look at the example we saw in the introduction:

```javascript
query {
  user: currentUserContext{
    ...on UserUser {
      name
    }
  }
}
```

and the response :

```javascript
{
  "data": {
    "user": {
      "name": "admin"
    }
  }
}
```

As we can see the data that we get back matches exactly the fields we asked for, in this case the name of the user. In this section we will analyse how we can query against any entity in Drupal. We will be making use of the GraphiQL explorer again, you can test any of the examples here by going to `/graphql/explorer` on your Drupal site.

In this section we will see how to query multiple parts of your drupal backend, and perform queries for nodes, taxonomy, routes etc.

