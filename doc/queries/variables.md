# Query variables

Until now we have looked at querie, but they are all static. We have been passing the values directly in the query to whatever we want to query. So how can we do the same but provide those values in a dynamic fashion? With variables.

The offical [GraphQL documentation](http://graphql.org/learn/queries/#variables) has a great overview of variables, but lets have a look at it here in any case.

## How to prepare the query to receive variables

The way to make your query ready to use variables is to provide a wrapper for it that takes the variables and injects them into the query. Lets take an example from the Query nodes examples where we wanted to query for nodes of type "Article" and make this type dynamic so we can pass whatever we want.

The old query looked like this :

```
query {
  nodeQuery(limit: 10, offset: 0, filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

And we can now refactor to look like this :

```
query getNodeType($type:String!, $limit:Int!, $offset:Int!) {
  nodeQuery(limit: $limit, offset: $offset, filter: {conditions: [{operator: EQUAL, field: "type", value: [$type]}]}) {
    entities {
      entityLabel
    }
  }
}
```

Here we go, now we can use the same query to retrieve "Articles", "Clients" or whatever else node type we want. We also provide variables for limit and offset, and because GraphQL is typed we easily can provie the type these parameters should be.

## Trying it out in GraphiQL

So get over to the GraphiQL and try out the query above, you will notice in the left bottom side there is a variables box, click on it and it will popup and fill the variables there like so :

```
{
    "type": "article",
    "limit": 10,
    "offset": 0
}
```
