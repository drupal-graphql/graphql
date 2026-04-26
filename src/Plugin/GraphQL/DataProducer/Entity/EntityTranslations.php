<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Language\LanguageInterface;
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
 * Returns all available translations of an entity.
 */
#[DataProducer(
  id: "entity_translations",
  name: new TranslatableMarkup("Entity translations"),
  description: new TranslatableMarkup("Returns all available translations of an entity"),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Translated entity"),
    multiple: TRUE,
    required: FALSE,
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE,
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL,
    ),
    "access_operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE,
      default_value: "view",
    ),
  ],
)]
class EntityTranslations extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * EntityTranslations constructor.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected EntityRepositoryInterface $entityRepository,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   *
   * @return array|null
   */
  public function resolve(EntityInterface $entity, ?bool $access, ?AccountInterface $accessUser, ?string $accessOperation, FieldContext $context): ?array {
    if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
      $languages = $entity->getTranslationLanguages();

      return array_map(function (LanguageInterface $language) use ($entity, $access, $accessOperation, $accessUser, $context) {
        $langcode = $language->getId();
        $entity = $entity->getTranslation($langcode);
        $entity->addCacheContexts(["static:language:{$langcode}"]);
        if ($access) {
          /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
          $accessResult = $entity->access($accessOperation, $accessUser, TRUE);
          $context->addCacheableDependency($accessResult);
          if (!$accessResult->isAllowed()) {
            return NULL;
          }
        }
        return $entity;
      }, $languages);
    }

    return NULL;
  }

}
