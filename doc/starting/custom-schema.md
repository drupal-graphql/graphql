# Creating a custom schema

The best way to start making a new schema is to take the example schema provided by the graphql_examples module in `/graphql/examples/` and copy all the files from the example module to a custom module of your own. By doing this you can then start adapting the schema to your needs including your own content types and making them available in the schema.

## Clone the SdlSchemaTest (deprecated)

First create your own module and call it `mydrupalgql`. Make sure you have a .info file inside the module to make sure drupal will know about this module (for more info see [Custom modules in drupal](https://www.drupal.org/docs/8/creating-custom-modules)).
Then head to the graphql module folder and copy the content of `src/Plugin/GraphQL/Schema/SdlSchemaTest.php`.
Inside `modules/mydrupalgql` create a file for your custom schema using this structure `src/Plugin/GraphQL/Schema/SdlSchemaMyDrupalGql.php` and paste the content of the previous file. Make sure to adapt the namespaces on the top of the file. In the end it should look something like this (some parts of the schema are marked with `...` for simplicity):

```php

namespace Drupal\mydrupalgql\Plugin\GraphQL\Schema;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;

/**
 * @Schema(
 *   id = "mydrupalgql",
 *   name = "My Drupal Graphql Schema"
 * )
 * @codeCoverageIgnore
 */
class SdlSchemaMyDrupalGql extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return <<<GQL
      schema {
        query: Query
      }

      type Query {
        article(id: Int!): Article
        page(id: Int!): Page
        node(id: Int!): NodeInterface
        label(id: Int!): String
      }

      type Article implements NodeInterface {
        id: Int!
        uid: String
        title: String!
        render: String
      }

      ...

      interface NodeInterface {
        id: Int!
      }
GQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([
      'Article' => ContextDefinition::create('entity:node')
        ->addConstraint('Bundle', 'article'),
      'Page' => ContextDefinition::create('entity:node')
        ->addConstraint('Bundle', 'page'),
    ]);

    ...

    return $registry;
  }
}
```

## Enable the custom schema

Go back to the list of servers under `/admin/config/graphql` to create a new server for your custom schema. When creating the server choose the "My Drupal Graphql Schema" as the schema. After saving click "Explorer". This will take you to the "GraphiQL" page, where you can explore your custom schema.
