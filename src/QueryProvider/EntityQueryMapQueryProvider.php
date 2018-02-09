<?php

namespace Drupal\graphql\QueryProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class EntityQueryMapQueryProvider implements QueryProviderInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a QueryProvider object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(array $params) {
    if (empty($params['version']) || empty($params['id'])) {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('graphql_query_map');
    /** @var \Drupal\graphql\Entity\QueryMapInterface $map */
    if ($map = $storage->load($params['version'])) {
      return $map->getQuery($params['id']);
    }

    return NULL;
  }
}
