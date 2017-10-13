<?php

namespace Drupal\graphql\QueryMapProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class EntityQueryMapProvider implements QueryMapProviderInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a QueryMapProvider object.
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
  public function getQuery($version, $id) {
    $storage = $this->entityTypeManager->getStorage('graphql_query_map');

    /** @var \Drupal\graphql\Entity\QueryMapInterface $map */
    if ($map = $storage->load($version)) {
      return $map->getQuery($id);
    }

    return NULL;
  }
}
