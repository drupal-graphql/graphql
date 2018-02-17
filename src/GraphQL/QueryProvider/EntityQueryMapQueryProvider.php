<?php

namespace Drupal\graphql\GraphQL\QueryProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use GraphQL\Server\OperationParams;

class EntityQueryMapQueryProvider implements QueryProviderInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * QueryProvider constructor.
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
  public function getQuery($id, OperationParams $operation) {
    list($version, $id) = explode(':', $id);

    // Check that the id is properly formatted.
    if (empty($version) || empty($id)) {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('graphql_query_map');
    /** @var \Drupal\graphql\Entity\QueryMapInterface $map */
    if ($map = $storage->load($version)) {
      return $map->getQuery($id);
    }

    return NULL;
  }
}
