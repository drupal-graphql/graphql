<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL type for Drupal entity routes.
 *
 * @GraphQLType(
 *   id = "entity_canonical_url",
 *   name = "EntityCanonicalUrl",
 *   description = @Translation("The canonical entity url."),
 *   interfaces = {"Url"},
 *   weight = 1
 * )
 */
class EntityCanonicalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveInfo $info = NULL) {
    if ($object instanceof Url && $object->isRouted()) {
      $parts = explode('.', $object->getRouteName());
      if (count($parts) !== 3) {
        return FALSE;
      }

      list($prefix, $entityType, $suffix) = $parts;
      $parameters = $object->getRouteParameters();

      if (($prefix === 'entity' && $suffix === 'canonical') && array_key_exists($entityType, $parameters)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
