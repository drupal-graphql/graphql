<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "entity_translations",
 *   name = @Translation("Entity translations"),
 *   description = @Translation("Returns all available translations of an entity"),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Translated entity"),
 *     multiple = TRUE,
 *     required = FALSE
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityTranslations extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityRepositoryInterface $entityRepository) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityRepository = $entityRepository;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\Core\Entity\TranslatableInterface|null
   */
  public function resolve(EntityInterface $entity) {
    if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
      $languages = $entity->getTranslationLanguages();

      return array_map(function (LanguageInterface $language) use ($entity) {
        $langcode = $language->getId();
        $entity = $entity->getTranslation($langcode);
        $entity->addCacheContexts(["static:language:{$langcode}"]);
        return $entity;
      }, $languages);
    }

    return NULL;
  }

}
