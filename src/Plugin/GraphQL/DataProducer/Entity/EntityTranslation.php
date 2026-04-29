<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the entity in a given language.
 */
#[DataProducer(
  id: "entity_translation",
  name: new TranslatableMarkup("Entity translation"),
  description: new TranslatableMarkup("Returns the translated entity."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Translated entity")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Language")
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL
    ),
    "access_operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE,
      default_value: "view"
    ),
  ],
)]
class EntityTranslation extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity.repository')
    );
  }

  /**
   * EntityTranslation constructor.
   */
  public function __construct(array $configuration, string $pluginId, PluginDefinitionInterface|array $pluginDefinition, EntityRepositoryInterface $entityRepository) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityRepository = $entityRepository;
  }

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity, string $language, ?bool $access, ?AccountInterface $accessUser, ?string $accessOperation, FieldContext $context): ?EntityInterface {
    if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
      $entity = $entity->getTranslation($language);
      $entity->addCacheContexts(["static:language:{$language}"]);
      // Check if the passed user (or current user if none is passed) has access
      // to the entity, if not return NULL.
      if ($access) {
        /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
        $accessResult = $entity->access($accessOperation, $accessUser, TRUE);
        $context->addCacheableDependency($accessResult);
        if (!$accessResult->isAllowed()) {
          return NULL;
        }
      }
      return $entity;
    }

    return NULL;
  }

}
