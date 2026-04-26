<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_examples\Wrappers\QueryConnection;
use Drupal\node\Entity\Node;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example data producer that loads a list of articles.
 */
#[DataProducer(
  id: "query_articles",
  name: "Load articles",
  description: "Loads a list of articles.",
  produces: new ContextDefinition(
    data_type: "any",
    label: "Article connection"
  ),
  consumes: [
    "offset" => new ContextDefinition(
      data_type: "integer",
      label: "Offset",
      required: FALSE
    ),
    "limit" => new ContextDefinition(
      data_type: "integer",
      label: "Limit",
      required: FALSE
    ),
  ]
)]
class QueryArticles extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const MAX_LIMIT = 100;

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
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityBuffer $entityBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
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
