<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for Drupal entity routes.
 *
 * @GraphQLType(
 *   id = "entity_canonical_url",
 *   name = "EntityCanonicalUrl",
 *   interfaces = {"Url"},
 *   weight = 1
 * )
 */
class EntityCanonicalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    if ($value instanceof Url && $value->isRouted()) {
      $parts = explode('.', $value->getRouteName());
      if (count($parts) !== 3) {
        return FALSE;
      }

      list($prefix, $entityType, $suffix) = $parts;
      $parameters = $value->getRouteParameters();

      if (($prefix === 'entity' && $suffix === 'canonical') && array_key_exists($entityType, $parameters)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
