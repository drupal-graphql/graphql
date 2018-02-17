# Filters

The GraphQL ships with integration with the EntityQuery to provide for some very complex filtering in queries. Its normal you need to do some complex scenarios like combining multiple filters with "AND" operators or "OR" operators,and even filter by fields in the Entities you querying like filtering for a field that is a entity reference field such as a taxonomy term field or a field with a relation to another node type. All of this is possible and we will see several examples of this in this article.

## Filter syntax

In GraphQL for Drupal you can have 3 parts to your filter

### conditions

Conditions is a list of filters that filter the query in a certain way for a given value on a given field. Lets look at an example of this

```
query {
  nodeQuery(filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

We are here filtering a list of articles. We basically filter the **type** of the node so that it is **EQUAL** to the value "article". You can provide multiple operators here : 

- EQUAL
- NOT_EQUAL
- SMALLER_THAN
- SMALLER_THAN_OR_EQUAL
- GREATER_THAN
- GREATER_THAN_OR_EQUAL
- IN
- NOT_IN
- LIKE
- NOT_LIKE
- BETWEEN
- NOT_BETWEEN
- IS_NULL
- IS_NOT_NULL

As you can see the potential here is very big. You can provide multiple conditions here and without specifying groups or conjunctions they will all be used as a "AND" combination meaning all must match in order for the query to pass. Lets look at how we can get more out of it with groups and conjunctions.

### conjunctions

What if we want to provide multiple filters but intead of them using the "AND" operator we want an "OR" combination? Thats what the conjuntions key is for, it can have two values

- AND
- OR

And when providing the conditions will be combine using this operator. In the example bellow we adapt the previous query to return all entities of type "Article" or "Client".

```
query {
  nodeQuery(filter: {conjunction:OR, conditions: [{ operator:EQUAL, field:"type", value:["article"] },{ operator:EQUAL, field:"type", value:["client"] }] }) {
    entities {
      entityLabel
    }
  }
}
```

### groups

In an ever more complex scenario we might actually have both things combined, wehere we want parts of a query where conditions should use the "OR" operator and also the "AND" operator. For these cases you can take advantage of groups.

Groups allow you to break down queries into different parts in order to achieve more complex queries just like you would in a normal database. When providing a group you can also provide a conjunction so that conditions inside a group are returned either using "OR" or "AND" operators. You can also have groups inside of groups so you can really go as deep as you want here. 

Lets look at an example where we want like previously all entities of type "Article" **OR** "Client" **AND** the status for both is **published**.

```
query {
  nodeQuery(filter: {conjunction: AND, 
    groups: [
      {conjunction: OR, conditions: [{operator: EQUAL, field: "type", value: ["article"]}, {operator: EQUAL, field: "type", value: ["client"]}]},
      {conditions: [{operator: EQUAL, field: "status", value: ["1"]}]}
    ]}) {
    entities {
      entityLabel
    }
  }
}
```

And this is it! We can make super complex queries using this syntax. Just keep in mind that this will be transformed into an EntityQuery and so you can easily relate this to how that query will end up like.