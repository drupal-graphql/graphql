<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;


use Drupal\Core\Entity\EntityInterface;

class EntityQueryValue {

  protected $entityType;

  protected $args;

  public function __construct($entityType, array $args = []) {
    $this->entityType = $entityType;
    $this->args = $args;
  }

  /**
   * Entity list resolver callback.
   */
  public function getEntityList($parent, array $args = NULL, $root) {
    $storage = \Drupal::entityTypeManager()->getStorage($this->entityType);
    $query = $storage->getQuery()->accessCheck(TRUE);

    $rangeArgs = array('offset', 'limit');
    $filterArgs = array_diff_key($args, array_flip($rangeArgs));
    foreach ($filterArgs as $key => $arg) {
      if (isset($arg) && isset($data['args'][$key])) {
        $arg = is_array($arg) && sizeof($arg) === 1 ? reset($arg) : $arg;
        $operator = is_array($arg) ? 'IN' : '=';
        $query->condition($data['args'][$key], $arg, $operator);
      }
    }

    if (!empty($args['offset']) || !empty($args['limit'])) {
      $query->range($args['offset'] ?: NULL, $args['limit'] ?: NULL);
    }

    $result = $query->execute();
    if (!empty($result)) {
      $entities = $storage->loadMultiple($result);

      // Filter entities that the current user doesn't have view access for.
      return array_filter($entities, function (EntityInterface $entity) {
        return $entity->access('view');
      });
    }

    return [];
  }

}
