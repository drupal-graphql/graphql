# Installation

The module requires installation via `composer`in order to pull in the dependencies for the module to work, most notably the [webonyx/graphql-php library](https://github.com/webonyx/graphql-php).

1. Install the module by running `composer require drupal/graphql:4.0.0-beta1`.
2. Enable the GraphQL module in extensions.
3. Login and navigate to `/admin/config/graphql` to create a new server.
4. At this point you can either start with the "Example schema" provided by the graphql_examples module (see the Introduction section) or start right away making your own custom schema as we will describe in the following sections.

## Dependencies

By installing the module with composer it will also install the necessary libraries [webonyx/graphql-php library](https://github.com/webonyx/graphql-php) and the [Typed Data module](https://www.drupal.org/project/typed_data) automatically.

## Permissions

At this point you can check the permissions added by the module in the permissions page at `/admin/people/permissions`. You can control who can perform arbitrary and persisted queries against graphql and also who can access the Voyager or the GraphiQL pages.

## Creating a schema

Like mentioned in the [Introduction](./../README.md) the 4.x version of the module uses a schema first approach which requires you to first start by making a schema and then start implementing how to resolve each of the fields of your schema. In the next chapter we will look at how to create your own schema to start resolving fields and returning actual Data from Drupal using the module.
