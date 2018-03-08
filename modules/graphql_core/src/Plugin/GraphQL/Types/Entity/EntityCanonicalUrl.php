<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL type for Drupal entity routes.
 *
 * @GraphQLType(
 *   id = "entity_canonical_url",
 *   name = "EntityCanonicalUrl",
 *   description = @Translation("The canonical entity url."),
 *   interfaces = {"InternalUrl"},
 *   weight = 1
 * )
 */
class EntityCanonicalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveContext $context, ResolveInfo $info) {
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
