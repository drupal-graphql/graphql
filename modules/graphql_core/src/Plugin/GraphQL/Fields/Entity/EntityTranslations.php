<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_translations",
 *   name = "entityTranslations",
 *   secure = true,
 *   type = "[Entity]",
 *   parents = {"Entity"}
 * )
 */
class EntityTranslations extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      $languages = $value->getTranslationLanguages();
      foreach ($languages as $language) {
        yield $value->getTranslation($language->getId());
      }
    }
  }

}
