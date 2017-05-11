<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve an entity translation.
 *
 * @GraphQLField(
 *   id = "entity_translation",
 *   name = "entityTranslation",
 *   nullable = true,
 *   multi = false,
 *   weight = -1,
 *   arguments = {
 *     "language" = "Languages"
 *   }
 * )
 *
 * not used now (deriver = "\Drupal\graphql_content\Plugin\Deriver\EntityTranslationDeriver")
 */
class EntityTranslation extends FieldPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityRepositoryInterface $entityRepository) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {

    // TODO Continue here. This class blows up EntityBasicFieldsTest. entity.repositry service might need special initialization in a Kernel test.

    if ($value instanceof EntityInterface) {
      yield $this->entityRepository->getTranslationFromContext($value, $args['langcode']);
    }
  }

}
