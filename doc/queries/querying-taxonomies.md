# Query Taxonomies

Another common entity in Drupal and one that provides great benefits to the usage of Drupal is taxonomy. Taxonomies can also be queried using GraphQL and the structure is very similar to how you query a node. For taxonomies the query **taxonomyTermQuery** and **taxonomyTermById** can be used to perform queries for a list of taxonomies terms or a single taxonomy term.

## Fetching a list of terms from a vocabulary

One common use case for taxonomies is to get a list of terms for a given vocabulary. Because we are using EntityQuery again the format will look very much like querying for nodes in the previous article. Here is what a query for terms of the vocabulary with **vid** "tags" looks like : 

```
query {
  taxonomyTermQuery(limit: 100, offset: 0, filter: {conditions: [{operator: EQUAL, field: "vid", value: ["tags"]}]}) {
    entities {
      entityLabel
    }
  }
}
```



