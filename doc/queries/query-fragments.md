# Fragments

GraphQL Fragments, as the name implies, are just pieces of a query. They mostly serve two purposes:

* Executing part of a query conditionally - only when the result is of a specified type. In the example above fieldSubtitle will be evaluated only when the node with id 1 is an Article. If it turns out to be a Basic Page, the fragment will be omitted and the response will just be one field shorter without raising any exceptions.
* Reusability. A fragment can be given a name and be used more than once.



Lets look at the following query : 

```
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

The query uses two fragments.

