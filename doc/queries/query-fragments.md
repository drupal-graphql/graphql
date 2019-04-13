# Query Fragments

GraphQL Fragments, as the name implies, are pieces of a query. They mostly serve two purposes:

* **Executing part of a query conditionally** - only when the result is of a specified type. In the example above fieldSubtitle will be evaluated only when the node with id 1 is an Article. If it turns out to be a Basic Page, the fragment will be omitted and the response will just be one field shorter without raising any exceptions.
* **Reusability**. A fragment can be given a name and be used more than once.

Lets look at the following query :

```graphql
{
  nodeById(id: "1", language: en) {
    ... on NodeArticle {
      fieldCategory {
        entity {
          ...termFragment
        }
      }
      fieldTags {
        entity {
          ...termFragment
        }
      }
    }
  }
}

fragment termFragment on TaxonomyTerm {
  name
  tid
}
```

There are two fragments in this query. The first one starting on line 3 is an **inline fragment**. We need it because fieldCategory and fieldTags are only attached to Articles and nodeById can return any node. The other one, defined on line 18, is a **named fragment** thanks to which we don't need to repeat the sub-queries for fieldCategory and fieldTags.

You can take advantage of fragments to make very complex easier to understand by breaking them down into smaller pieces, they are also a very good way to share common things like we see above the termFragment, make the code cleaner and easier to refactor.

