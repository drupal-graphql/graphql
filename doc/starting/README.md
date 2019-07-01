# Installation

The module requires installation via `composer`in order to pull in the dependencies for the module to work, most notably the [webonyx/graphql-php library](https://github.com/webonyx/graphql-php).

1. Install the module by running `composer require drupal/graphql:4.x-dev`.
2. Enable the GraphQL module in extensions.
3. Login and navigate to `/admin/config/graphql` to create a new server.
4. At this point you can either start with the "Example schema" provided by the graphql_examples module (see the Introduction section) or start right away making your own custom schema as we will describe in the following sections.

## Dependencies

By installing the module with composer it will also install the necessary libraries [webonyx/graphql-php library](https://github.com/webonyx/graphql-php) and the [Typed Data module](https://www.drupal.org/project/typed_data) automatically.

## Permissions

At this point you can check the permissions added by the module in the permissions page at `/admin/people/permissions`. You can control who can perform arbitrary and persisted queries against graphql and also who can access the Voyager or the GraphiQL pages.
