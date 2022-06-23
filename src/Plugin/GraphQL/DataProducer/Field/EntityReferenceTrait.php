<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Entity reference helpers.
 */
trait EntityReferenceTrait {

  /**
   * Retrieves referenced entities from the given resolver.
   *
   * May optionally respect bundles/language and perform access checks.
   *
   * @param string $type
   *   Entity type ID.
   * @param string|null $language
   *   Optional. Language to be respected for retrieved entities.
   * @param array|null $bundles
   *   Optional. List of bundles to be respected for retrieved entities.
   * @param bool $access
   *   Whether to filter out inaccessible entities.
   * @param \Drupal\Core\Session\AccountInterface|null $accessUser
   *   User entity to check access for. Default is null.
   * @param string $accessOperation
   *   Operation to check access for. Default is view.
   * @param \Closure $resolver
   *   The resolver to execute.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The list of references entities.
   */
  protected function getReferencedEntities(string $type, ?string $language, ?array $bundles, bool $access, ?AccountInterface $accessUser, string $accessOperation, \Closure $resolver, FieldContext $context): array {
    $entities = $resolver() ?: [];

    if (isset($bundles)) {
      $entities = array_filter($entities, function (EntityInterface $entity) use ($bundles) {
        return in_array($entity->bundle(), $bundles);
      });
    }
    if (isset($language)) {
      $entities = $this->getTranslated($entities, $language);
    }
    if ($access) {
      $entities = $this->filterAccessible($entities, $accessUser, $accessOperation, $context);
    }

    if (empty($entities)) {
      $type = $this->entityTypeManager->getDefinition($type);
      /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
      $tags = $type->getListCacheTags();
      $context->addCacheTags($tags);
      return [];
    }

    return $entities;
  }

  /**
   * Get the referenced entities in the specified language.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   Entities to process.
   * @param string $language
   *   Language to be respected for retrieved entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Translated entities.
   */
  private function getTranslated(array $entities, string $language): array {
    return array_map(function (EntityInterface $entity) use ($language) {
      if ($language !== $entity->language()->getId() && $entity instanceof TranslatableInterface && $entity->hasTranslation($language)) {
        $entity = $entity->getTranslation($language);
      }
      $entity->addCacheContexts(["static:language:{$language}"]);
      return $entity;
    }, $entities);
  }

  /**
   * Filter out not accessible entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   Entities to filter.
   * @param \Drupal\Core\Session\AccountInterface|null $accessUser
   *   User entity to check access for. Default is null.
   * @param string $accessOperation
   *   Operation to check access for. Default is view.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Filtered entities.
   */
  private function filterAccessible(array $entities, ?AccountInterface $accessUser, string $accessOperation, FieldContext $context): array {
    return array_filter($entities, function (EntityInterface $entity) use ($accessOperation, $accessUser, $context) {
      /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
      $accessResult = $entity->access($accessOperation, $accessUser, TRUE);
      $context->addCacheableDependency($accessResult);
      if (!$accessResult->isAllowed()) {
        return FALSE;
      }
      return TRUE;
    });
  }

}
