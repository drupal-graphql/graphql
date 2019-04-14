# Querying taxonomies

Another common entity in Drupal and one that provides great benefits to the usage of Drupal is taxonomy. Taxonomies can also be queried using GraphQL and the structure is very similar to how you query a node. For taxonomies the query **taxonomyTermQuery** and **taxonomyTermById** can be used to perform queries for a list of taxonomies terms or a single taxonomy term.

## Querying a list of terms from a vocabulary

One common use case for taxonomies is to get a list of terms for a given vocabulary. Because we are using EntityQuery again the format will look very much like querying for nodes in the previous article. Here is what a query for terms of the vocabulary with **vid** "tags" looks like :

```graphql
query {
  taxonomyTermQuery(limit: 10, offset: 0, filter: {conditions: [{operator: EQUAL, field: "vid", value: ["tags"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

This will fetch for a limit of 10 terms that belong to the vocabulary "tags". The result will once again be very much like what we asked for :

```javascript
{
  "data": {
    "taxonomyTermQuery": {
      "entities": [
        {
          "entityLabel": "Drupal"
        },
        {
          "entityLabel": "GraphQL"
        },
        {
          "entityLabel": "Web"
        },
        {
          "entityLabel": "React"
        }
      ]
    }
  }
}
```

## Querying a single term by its id

Querying a single term can be done by taking advantage of the query **taxonomyTermById** which takes as argument the id for the term we want to get.

```graphql
query {
  taxonomyTermById(id: "3") {
    entityLabel
  }
}
```

And the result of this query will be whatever we asked for, in this case the entityLabel alone :

```javascript
{
  "data": {
    "taxonomyTermById": {
      "entityLabel": "Drupal"
    }
  }
}
```

Once again, you can query multiple things inside this query so make sure to try GraphiQL and explore what items can be fetched.

