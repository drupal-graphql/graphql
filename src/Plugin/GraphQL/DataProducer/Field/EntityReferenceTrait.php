<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;

trait EntityReferenceTrait {

  protected function getReferencedEntities($type, $language, $bundles, $access, $accessUser, $accessOperation, $resolver, $context) {
    $entities = $resolver() ?: [];

    // Get the correct translation.
    if (isset($language)) {
      $entities = array_map(function (EntityInterface $entity) use ($language) {
        if ($language !== $entity->language()->getId() && $entity instanceof TranslatableInterface && $entity->hasTranslation($language)) {
          $entity = $entity->getTranslation($language);
        }

        $entity->addCacheContexts(["static:language:{$language}"]);
        return $entity;
      }, $entities);
    }

    $entities = array_filter($entities, function (EntityInterface $entity) use ($bundles, $access, $accessOperation, $accessUser, $context) {
      if (isset($bundles) && !in_array($entity->bundle(), $bundles)) {
        return FALSE;
      }

      // Check if the passed user (or current user if none is passed) has
      // access to the entity, if not return NULL.
      if ($access) {
        /* @var $accessResult \Drupal\Core\Access\AccessResultInterface */
        $accessResult = $entity->access($accessOperation, $accessUser, TRUE);
        $context->addCacheableDependency($accessResult);
        if (!$accessResult->isAllowed()) {
          return FALSE;
        }
      }

      return TRUE;
    });

    if (empty($entities)) {
      $type = $this->entityTypeManager->getDefinition($type);
      /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
      $tags = $type->getListCacheTags();
      $context->addCacheTags($tags);
      return NULL;
    }

    return $entities;
  }

}
