# Querying nodes

One of the most fundamental entities in Drupal is the node entity. It is a building block for any Drupal site a flexible system to allow querying against any type of node entity \(bundles\) is fundamental. The GraphQL module takes advantage of the [EntityQuery](https://api.drupal.org/api/drupal/core!lib!Drupal.php/function/Drupal%3A%3AentityQuery/8.2.x) to allow super flexible querying and listing of entities. In the Drupal GraphQL module when you want to perform a query against a node entity you perform what is called a **nodeQuery.**

## Querying a list of nodes

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

The response to the above query is going to return exactly what we mentioned we wanted inside the entities item, the entityLabel.

```
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

Another common scenario is needing to fetch a single node by its id. In the GraphQL module this can be done by taking advantage of another query type called the **nodeById**

Here is a simple example returning the node with id : 1

```
query {
  nodeById(id: "1") {
    entityLabel
    entityBundle
  }
}
```

Simple right? Now what we get in response its again what we asked for in the query fields, in this case the entityLabel and the entityBundle :

```
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

Lets now explore a bit more about querying nodes and how the queryNode structure is used in the GraphQL module.

## Naming

GraphQL naming conventions are slightly different than Drupal's.

* Fields and properties are in camelCase. This means that **field\_image** in Drupal becomes **fieldImage** in GraphQL and the **revision\_log** property becomes **revisionLog**.
* Entity types and bundles use camelCase with the first letter capitalized so **taxonomy\_term** becomes **TaxonomyTerm** and the tags vocabulary becomes **TaxonomyTermTags**. As we can see bundles are prefixed with the entity type name. 

This is important so that you know what to expect when searching for fields in queries, however remember what we said that GraphiQL is there to help and so all you need sometimes is a little push by entering "cmd+space" in order to see which fields are available for you to query.

## Fields inside a query

GraphQL has the potential to go and fetch fields from very different places without the need for extra requests, thats one of the benefits of using such a query language. Lets look at this example :

```
query {
  nodeById(id: "1", language: en) {
    entityId
    entityCreated

    title
    status

    ... on NodeArticle {
      fieldSubtitle
    }
  }
}
```

The query above fetches information from 3 different places :

* **entityId** and **entityCreated** come from the Entity Interface. These fields are available for all entity objects. nodeById query returns a Node Interface which extends Entity Interface.
* title and status are defined in the Node Interface and are available for all nodes, regardless of their content type.
* **fieldSubtitle** is a field \(**field\_subtitle** in Drupal\) that has been added to the Article content type. It's not a part of neither Node nor Entity Interfaces, it is only available in the NodeArticle Type. **nodebyId** can return any node, not just Article, so we need to wrap the fieldSubtitle in a [GraphQL Fragment](http://graphql.org/learn/queries/#fragments).

If we paste the above query in GraphiQL we will get the following result : 

```
{
  "data": {
    "nodeById": {
      "entityId": "1",
      "entityCreated": "2017-12-01T00:00:00+0100",
      "title": "GraphQL rocks",
      "status": 1,
      "fieldSubtitle": "Try it out!"
    }
  }
}
```



