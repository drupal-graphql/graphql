<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_examples\Wrappers\QueryConnection;
use Drupal\node\Entity\Node;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example data producer that loads a list of articles.
 *
 * @DataProducer(
 *   id = "query_articles",
 *   name = @Translation("Load articles"),
 *   description = @Translation("Loads a list of articles."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Article connection")
 *   ),
 *   consumes = {
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class QueryArticles extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const int MAX_LIMIT = 100;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The entity buffer service.
   */
  protected EntityBuffer $entityBuffer;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityBuffer $entityBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityBuffer = $entityBuffer;
  }

  /**
   * Resolves the query by preparing an entity query for executing later.
   */
  public function resolve(int $offset, int $limit, RefinableCacheableDependencyInterface $metadata): QueryConnection {
    if ($limit > static::MAX_LIMIT) {
      throw new UserError(sprintf('Exceeded maximum query limit: %s.', static::MAX_LIMIT));
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $entityType = $storage->getEntityType();
    $query = $storage->getQuery()
      ->currentRevision()
      ->accessCheck()
      // The access check does not filter out unpublished nodes automatically,
      // so we need to do this explicitly here. We don't want to run access
      // checks on loaded nodes later, as that would then make the query count
      // numbers wrong. Therefore all fields relevant for access need to be
      // included here.
      ->condition('status', Node::PUBLISHED);

    $query->condition($entityType->getKey('bundle'), 'article');
    $query->range($offset, $limit);

    $metadata->addCacheTags($entityType->getListCacheTags());
    $metadata->addCacheContexts($entityType->getListCacheContexts());

    return new QueryConnection($query, $this->entityBuffer);
  }

}
