# Mutations

In version 4 of Drupal GraphQL `Mutations` work a lot more similar to queries than they do in 3.x. Mutations are also technically Data producers which we already looked at.

Lets create a mutation that creates a new article. In this case it takes a data parameter that can have a `title` and a `creator` in order to set these fields when creating the new article if they have been provided.

Similar to queries we can start by adding the necessary schema information, not only to register our new mutation but also provide type safety on all parameters as well. This mutation will return the newly created "Article".

 The code with all the demo queries and mutations in these docs can be found in [this repository](https://github.com/joaogarin/mydrupalgql).

## Add the schema declaration

```
schema {
    mutation: Mutation
}

type Mutation {
    createArticle(data: ArticleInput): Article
}

type Article implements NodeInterface {
    id: Int!
    title: String!
    creator: String
}

interface NodeInterface {
    id: Int!
}

input ArticleInput {
    title: String!
    description: String
}

```

We can now see we have a Mutation called `createArticle` which takes a data parameter, but because GraphQL is heavily typed we know everything we can and must include in the new Article like the title which is mandatory in this case.

## Implement the custom data producer (mutation)

We now need to implement the actual mutation, in the file `src/Plugin/GraphQL/DataProducer` we include the following file `CreateArticle.php` :

```php
<?php

namespace Drupal\mydrupalgql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a new article entity.
 *
 * @DataProducer(
 *   id = "create_article",
 *   name = @Translation("Create Article"),
 *   description = @Translation("Creates a new article."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Article")
 *   ),
 *   consumes = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Article data")
 *     )
 *   }
 * )
 */
class CreateArticle extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * CreateArticle constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * Creates an article.
   * 
   * @param array $data
   *   The title of the job.
   *
   * @return \Drupal\node\NodeInterface
   *   The newly created article.
   *
   * @throws \Exception
   */
  public function resolve(array $data) {
    if ($this->currentUser->hasPermission("create article content")) {
        $values = [
            'title' => $data['title'],
            'field_article_creator' => $data['creator'],
        ];
        $node = Node::create($values);
        $node->save();
        return $node;
    }
    return NULL;
  }

}

```

## Adding resolvers

To add the resolvers we go to our schema implementation and call the created data producer `create_article` inside the `getResolverRegistry` method.

```php
/**
 * {@inheritdoc}
 */
protected function getResolverRegistry() {
  
  ...
  // Create article mutation.
  $registry->addFieldResolver('Mutation', 'createArticle',
    $builder->produce('create_article')
      ->map('data', $builder->fromArgument('data'))
  );

  ...
  return $registry;
}
```

This mutation can now be called like this :
 
```graphql
mutation {
  createArticle(data: {title: "Hello GraphQl 2"}) {
    ... on Article {
      id
      title
    }
  }
}
```

and should return something like : 

```json
{
  "data": {
    "createArticle": {
      "id": 2,
      "title": "Hello GraphQl 2"
    }
  }
}
```
