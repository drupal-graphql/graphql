# Querying the node entity

One of the most fundamental entities in Drupal is the node entity. It is a building block for any Drupal site a flexible system to allow querying against any type of node entity \(bundles\) is fundamental. The GraphQL module takes advantage of the [EntityQuery](https://api.drupal.org/api/drupal/core!lib!Drupal.php/function/Drupal%3A%3AentityQuery/8.2.x) to allow super flexible querying and listing of entities. In the Drupal GraphQL module when you want to perform a query against a node entity you perform what is called a **nodeQuery.**

Here is a super simple example of how to list 10 nodes of type "Article" : 

```
query {
  nodeQuery(limit: 10, offset: 0, filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

Lets analyse this query a bit better. It performs a query to return 10 nodes of type article. The syntax for the filters might seem a bit too complex \(it might sound familiar if you are familiar with EntityQuery\) but it provides a super powerful way of doing any type of complex query.

