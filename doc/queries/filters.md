# Filters

The GraphQL module ships with integration with the EntityQuery to provide for some very complex filtering in queries. Its normal we need to do some complex scenarios like combining multiple filters with "AND" operators or "OR" operators,and even filter by fields in the Entities we query like filtering for a field that is a entity reference field such as a taxonomy term field or a field with a relation to another node. All of this is possible and we will see several examples of this in this article.

## Filter syntax

In GraphQL for Drupal you can have 3 parts to your filter

### conditions

Conditions is a list of filters that filter the query in a certain way for a given value on a given field. Lets look at an example of this

```graphql
query {
  nodeQuery(filter: {conditions: [{operator: EQUAL, field: "type", value: ["article"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

We are here filtering a list of articles. We basically filter the **type** of the node so that it is **EQUAL** to the value "article". You can provide one of many operators :

* EQUAL
* NOT\_EQUAL
* SMALLER\_THAN
* SMALLER\_THAN\_OR\_EQUAL
* GREATER\_THAN
* GREATER\_THAN\_OR\_EQUAL
* IN
* NOT\_IN
* LIKE
* NOT\_LIKE
* BETWEEN
* NOT\_BETWEEN
* IS\_NULL
* IS\_NOT\_NULL

As you can see the potential here is very big. We can provide multiple conditions here and without specifying groups or conjunctions they will all be used as a "AND" combination meaning all must match in order for the query to pass. Lets look at how we can get more out of it with groups and conjunctions.

### conjunctions

What if we want to provide multiple filters but intead of them using the "AND" operator we want an "OR" combination? Thats what the conjuntions key is for, it can have two values

* AND
* OR

And when providing the conditions will be combine using this operator. In the example bellow we adapt the previous query to return all entities of type "Article" or "Client".

```graphql
query {
  nodeQuery(filter: {conjunction:OR, conditions: [{ operator:EQUAL, field:"type", value:["article"] },{ operator:EQUAL, field:"type", value:["client"] }] }) {
    entities {
      entityLabel
    }
  }
}
```

### groups

In an ever more complex scenario we might actually have both things combined, wehere we want parts of a query where conditions should use the "OR" operator and also the "AND" operator. For these cases we can take advantage of groups.

Groups allow us to break down queries into different parts in order to achieve more complex queries just like we would in a normal database. When providing a group we can also provide a conjunction so that conditions inside a group are returned either using "OR" or "AND" operators. We can also have groups inside of groups so we can really go as deep as we want here.

Lets look at an example where we want like previously all entities of type "Article" **OR** "Client" **AND** the status for both is **published**.

```graphql
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

And this is it! We can make super complex queries using this syntax. Just keep in mind that this will be transformed into an EntityQuery and so it easily relate this to how that query will end up like.

## Filtering for fields in the entity

A very common scenario is wanting to filter for the value of a given field, we saw before how to filter for the type of the entity. Lets have a look how we can filter for a value of a field in the entity. Let's adapt the first example to filter for the value of the status instead of the type.

```graphql
query {
  nodeQuery(filter: {conditions: [{operator: EQUAL, field: "status", value: ["1"]}]}) {
    entities {
      entityLabel
    }
  }
}
```

Here we get all entities which are published.

## Filtering for custom fields in the entity

If we want to filter for a field that exists only within that entity, its not very different. Lets look at a simple example. Lets imagine we have an entity "Client" which has a custom telephone number field :

```graphql
query {
  nodeQuery(filter: {conditions: [
    {operator: EQUAL, field: "type", value: ["client"]},
    {operator: EQUAL, field: "telephone", value: ["918273736"]}
  ]}) {
    entities {
      entityLabel
    }
  }
}
```

We can easily filter by this field just by providing its field name and value, together with the operator we want.

## Filtering for entity reference fields

Yet another common scenario is we need to filter for a field, but this field is a entity reference meaning that we should provide the key of the entity to be referenced in the value property. In this example lets imagine we have a entity reference in the "Article" entity to a "Client", via the `field_client` field. This field is an entity reference of type node. The way we filter for this field is as followed :

```graphql
query {
  nodeQuery(filter: {conditions: [
    {operator: EQUAL, field: "type", value: ["article"]},
    {operator: EQUAL, field: "field_client.entity.nid", value: ["13"]}
  ]}) {
    entities {
      entityLabel
    }
  }
}
```

If the entity we are filtering is for example of type "Term reference" then the `field_client.entity.nid` should become `field_client.entity.tid` as it now should reference a term id and not a node id.

