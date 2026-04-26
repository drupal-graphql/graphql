<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_composable\GraphQL\Response\ArticleResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a new article entity.
 */
#[DataProducer(
  id: 'create_article',
  name: new TranslatableMarkup('Create Article'),
  description: new TranslatableMarkup('Creates a new article.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Article'),
  ),
  consumes: [
    'data' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Article data'),
    ),
  ],
)]
class CreateArticle extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected AccountInterface $currentUser,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Creates an article.
   *
   * @param array $data
   *   The submitted values for the article.
   *
   * @return \Drupal\graphql_composable\GraphQL\Response\ArticleResponse
   *   The newly created article.
   *
   * @throws \Exception
   */
  public function resolve(array $data): ArticleResponse {
    $response = new ArticleResponse();
    if ($this->currentUser->hasPermission("create article content")) {
      $values = [
        'type' => 'article',
        'title' => $data['title'],
        'body' => $data['description'],
      ];
      $node = Node::create($values);
      $node->save();
      $response->setArticle($node);
    }
    else {
      $response->addViolation(
        $this->t('You do not have permissions to create articles.')
      );
    }
    return $response;
  }

}
