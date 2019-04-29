# Drupal GraphQL

## Introduction

The GraphQL Drupal module lets you query or mutate \(update/delete\) any content or configuration using the official GraphQL query language. This is an extremely powerful tool which opens the door for Drupal to be used in a multitude of applications.

## Who is this module for?

Anyone who wants to get JSON data out of Drupal.

A few examples of where the GraphQL module could be used:

* Decoupled Drupal applications with a javascript front-end \(React, Angular, Ember, etc\), 
* Twig Templates \(Drupal theming\)
* Mobile applications that need a persistent data store
* IOT data storage

## Hello World \(Quick Start\)

1. Familiarize yourself with the GraphQL language. The official GraphQL docs are very well written. 

   [http://graphql.org/learn/](http://graphql.org/learn/)

2. Install the module and enable GraphQL, as well as GraphQL Core \(machine names are `graphql` and `graphql_core` respectively\).
3. Login and navigate to `/graphql/explorer` \(Configuration &gt; Web Services &gt; GraphQL &gt; Schemas &gt; Explorer\)

   This will bring you to the GraphiQL explorer.

4. **Read the comments** and then enter the following query in the left pane:

   ```graphql
     query {
       user: currentUserContext{
         ...on User {
           name
         }
       }
     }
   ```

5. Press `Ctrl-Space` and you should see something like the following display in the right pane:

   ```javascript
    {
      "data": {
        "user": {
            "name": "admin"
      }
    }
   ```

6. Congrats! You just figured out how to execute your first GraphQL query. This query is displaying the current logged in user, you.

**NOTES:**

* The GraphiQL explorer, included with the module, is your friend, it’s amazing. You will most likely use the GraphiQL explorer to build and test more complicated queries. 
* GraphQL is introspective, meaning that the entire schema \(data model\) is known up front. This is important as it allows tools like GraphiQL to implement autocompletion. 
* You can use GraphiQL to explore your way through the data and configuration, once you know the basic GraphQL syntax. You can use the tab key in the explorer like you would with autocompletion or intellisense in modern IDEs.
* The `... User` in the query above is a fragment which exposes all of the fields on the User entity to us. Inline fragments like this can be a very powerful way to explore the schema. 

**Resources**

* [https://github.com/drupal-graphql/graphql](https://github.com/drupal-graphql/graphql)
* [https://drupal.slack.com](https://drupal.slack.com) - The GraphQL Slack Channel is very active

