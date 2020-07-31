# Creating a custom schema

The best way to start making a new schema is to take the example schema provided by the graphql_examples module in `/graphql/examples/` and copy all the files from the example module to a custom module of your own. By doing this you can then start adapting the schema to your needs including your own content types and making them available in the schema.

## Enable the custom schema

Go back to the list of servers under `/admin/config/graphql` to create a new server for your custom schema. When creating the server choose the "My Drupal Graphql Schema" as the schema. After saving click "Explorer". This will take you to the "GraphiQL" page, where you can explore your custom schema.

## Adapt module to your needs

Inside `/graphql` you will find some files that are a common `.graphqls` file that your editor will likely pick up as GraphQL files already. They include the schema definition for your custom schema.

### example.graphqls

This is the main entry point for your schema. You can insert new types and fields into types here as needed for your use case. Simply adding new types and fields here will not affect the API right away, they will be available but will not yet resolve anything, as we didn't add any resolvers yet.

If your requirements are simple this file should be enough to get started.

### Extensions

`example.extension.base.graphqls` and `example_extension.extension.graphqls` are files that can be added on top of the existing schema file to "extend" the schema with new functionality. We will approach this in the Advanced section when talking about spliting schemas so that you can make certain modules enable new functionalities as they are enabled.
