# Drupal GraphQL

## Introduction

The GraphQL Drupal module lets you query or mutate \(update/delete\) any content or configuration using the official GraphQL query language. This is an extremely powerful tool which opens the door for Drupal to be used in a multitude of applications.

## Who is this module for?

Anyone who wants to get JSON data out of Drupal.

A few examples of where the GraphQL module could be used:

* Decoupled Drupal applications with a javascript front-end \(React, Angular, Vue, etc\),
* Twig Templates \(Drupal theming\)
* Mobile applications that need a persistent data store
* IOT data storage

### Drupal GraphQL 5.x

This documentation refers to the 5.x version of the module which uses a schema first approach, where you will need to specify the schema in a DSL format. The documentation will guide you through the process of doing that. The benefit is that the end resulting schema is a lot cleaner and includes only what you explicitly include in the schema, and not the whole of Drupal's data structures.

This trade-off does come with costs mostly around ease of use - if you prefer automatic generation of the schema then take a look at the [GraphQL Compose](https://www.drupal.org/project/graphql_compose) or [GraphQL Core Schema](https://www.drupal.org/project/graphql_core_schema) projects which have implemented automatic schema generation on top of the GraphQL module.

## Hello World \(Quick Start\)

1. Familiarize yourself with the GraphQL language. The official GraphQL docs are very well written.

   [http://graphql.org/learn/](http://graphql.org/learn/)

2. Install the module and enable GraphQL.
3. Login and navigate to `/admin/config/graphql` create a new server. You can use the "Example schema" that comes with the graphql_examples module (comes with the graphql module but needs to be enabled separately) to try out using GraphQL for the first time before making your own schema. Create a server and specify an endpoint such as `/graphql`. After creating the server click on `explorer` and this should bring you to the Graphiql explorer.

To be able to query something you first have to create an Article in the Drupal backend.

4. **Read the comments** and then enter the following query in the left pane:

   ```graphql
     query {
       article(id: 1){
         id
         title
       }
     }
   ```

5. Press `Ctrl-Space` and you should see something like the following display in the right pane:

   ```javascript
    {
      "data": {
        "article": {
            "id": 1,
            "title: "Hello GraphQL"
      }
    }
   ```

6. Congratulations!! You just figured out how to execute your first GraphQL query. This query is displaying a list of articles.

**NOTES:**

* The GraphiQL explorer, included with the module, is your friend, it’s amazing. You will most likely use the GraphiQL explorer to build and test more complicated queries.
* GraphQL is introspective, meaning that the entire schema \(data model\) is known up front. This is important as it allows tools like GraphiQL to implement autocompletion.
* You can use GraphiQL to explore your way through the data and configuration, once you know the basic GraphQL syntax. You can use the tab key in the explorer like you would with autocompletion or intellisense in modern IDEs.
