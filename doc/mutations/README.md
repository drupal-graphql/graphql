# Mutations

In GraphQL, a mutation is the terminology used whenever you want to add, modify, or delete data stored on the server, in this case Drupal.

Unfortunately, the module does not include a way to peform common mutations out of the box due to some technical requirements of graphql. You can read more about this at the amazeelabs blog post below.

Mutations must be created in a custom module. In many cases you will extend existing provided classes for adding, updating, or deleting an entity. Specifically CreateEntityBase, DeleteEntityBase, or UpdateEntityBase.

A fantastic resource for implementing mutations can be found at [https://www.amazeelabs.com/en/journal/extending-graphql-part-3-mutations](https://www.amazeelabs.com/en/journal/extending-graphql-part-3-mutations)

The corresponding example code for creating, deleting, updating, and fileuploads, can be found here: [https://github.com/drupal-graphql/graphql-examples](https://github.com/drupal-graphql/graphql-examples)

A simple mutation to add an article content type \(node entity / article bundle\) might look the following:

```graphql
mutation {
  addArticle(input: { title: "Hey" }) {
    errors
    violations {
      message
      code
      path
    }
    article: entity {
      ... on NodeArticle {
        nid
      }
    }
  }
}
```

The specific returned fields on article are up to you and are specified via the object syntax following the mutation call `addArticle(input: {title: "Hey"}) {`. The input parameter is defined as an object of corresponding fields that match the fields in your content type. The error and violoations fields, are optional but can be helpful in determining whether or not something executes as intendend.

In the mutation above, we use the inline fragment `... on NodeArticle { nid } to return the resulting nid of the created article.` We use the alias `article` for the returned entity to make the result a bit more friendly.

The result of the above mutation would look something like this:

```json
{
  "data": {
    "addArticle": {
      "errors": [],
      "violations": [],
      "article": {
        "nid": 15
      }
    }
  }
}
```

External Resources:

* [http://graphql.org/learn/queries/\#mutations](http://graphql.org/learn/queries/#mutations)
* [https://www.amazeelabs.com/en/journal/extending-graphql-part-3-mutations](https://www.amazeelabs.com/en/journal/extending-graphql-part-3-mutations)
