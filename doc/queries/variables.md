# Variables

Until now we have looked at queries, but they are all static. We have been passing the values directly in the query to whatever we want to fetch. So how can we do the same but provide those values in a dynamic fashion? With variables.

The offical [GraphQL documentation](http://graphql.org/learn/queries/#variables) has a great overview of variables, but lets have a look at it here in any case.

## How to prepare the query to receive variables

The way to make your query ready to use variables is to provide the variables as parameters for the query and then using it inside. Let's take an example from the "Querying nodes" examples where we wanted to query for nodes of type "Article" and make this type dynamic so we can pass whatever we want.

The old query looked like this :

```graphql
query {
  nodeQuery(limit: 10, offset: 0, filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

And we can now refactor it to look like this :

```graphql
query getNodeType($type:String!, $limit:Int!, $offset:Int!) {
  nodeQuery(limit: $limit, offset: $offset, filter: {conditions: [{operator: EQUAL, field: "type", value: [$type]}]}) {
    entities {
      entityLabel
    }
  }
}
```

Here we go, now we can use the same query to retrieve "Articles", "Clients" or whatever else node **type** we want. We also provide variables for **limit** and **offset**, and because GraphQL is typed we have to provide it with the type these variables should be.

## Trying it out in GraphiQL

So get over to the GraphiQL by navigating to **graphql/explorer** and try out the query above, you will notice in the left bottom side there is a **variables** box, click on it and it will pop open and fill the variables there like so :

```javascript
{
    "type": "article",
    "limit": 10,
    "offset": 0
}
```

