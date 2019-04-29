# Querying nodes

One of the most fundamental entities in Drupal is the node entity. It is a building block for any Drupal site a flexible system to allow querying against any type of node entity \(bundles\) is fundamental. The GraphQL module takes advantage of the [EntityQuery](https://api.drupal.org/api/drupal/core!lib!Drupal.php/function/Drupal%3A%3AentityQuery/8.2.x) to allow super flexible querying and listing of entities. In the Drupal GraphQL module when you want to perform a query against a node entity you perform what is called a **nodeQuery.**

## Querying a list of nodes

Here is a super simple example of how to list 10 nodes of type "Article" :

```graphql
query {
  nodeQuery(limit: 10, offset: 0, filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

Lets analyse this query a bit better. It performs a query to return 10 nodes of type article. The syntax for the filters might seem a bit too complex \(it might sound familiar if you are familiar with EntityQuery\) but it provides a super powerful way of doing any type of complex query.

The response to the above query is going to return exactly what we mentioned we wanted inside the entities item, the entityLabel.

```javascript
{
  "data": {
    "nodeQuery": {
      "entities": [
        {
          "entityLabel": "10 Reasons why you should be using GraphQL"
        },
        {
          "entityLabel": "Drupal and GraphQL, a love story"
        }
      ]
    }
  }
}
```

As we can see the result just fills the entities array with the information we wanted out of each node. Lets then look at a bit more complex queries regarding nodes.

### A note on filters

The GraphQL module allows for very complex type of filters, for a deeper look into filters checkout the Filters guide in this section.

## Querying a single node by its node id

Another common scenario is needing to fetch a single node by its id. In the GraphQL module this can be done by taking advantage of another query called the **nodeById**

Here is a simple example returning the node with id : 1

```graphql
query {
  nodeById(id: "1") {
    entityLabel
    entityBundle
  }
}
```

Simple right? Now what we get in response its again what we asked for in the query fields, in this case the entityLabel and the entityBundle :

```graphql
{
  "data": {
    "nodeById": {
      "entityLabel": "10 Reasons why you should be using GraphQL",
      "entityBundle": "article"
    }
  }
}
```

As you can see you can exactly map the response to what was asked in the query making it very intuitive to ask for new things and now what to expect in the resulting format.

