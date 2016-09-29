<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;

class EntityByUuidField extends FieldBase implements ContainerAwareInterface, RefinableCacheableDependencyInterface {

  use ContainerAwareTrait;
  use RefinableCacheableDependencyTrait;

  /**
   * The entity type handled by this field instance.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Constructs an EntityByUuidField object.
   *
   * @param array $entityType
   *   The entity type handled by this field instance.
   * @param \Youshido\GraphQL\Type\TypeInterface $outputType
   *   The output type of the field.
   */
  public function __construct($entityType, TypeInterface $outputType) {
    $this->entityType = $entityType;

    // Generate a human readable name from the entity type.
    $typeName = StringHelper::formatPropertyName($entityType);

    $config = [
      'name' => "{$typeName}ByUuid",
      'type' => $outputType,
      'args' => [
        'uuid' => new NonNullType(new StringType()),
      ],
    ];

    parent::__construct($config);
  }

  /**
   * Resolve function for this field.
   *
   * Loads an entity by its entity id.
   *
   * @param $value
   *   The parent value. Irrelevant in this case.
   * @param array $args
   *   The array of arguments. Contains the id of the entity to load.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The context information for which to resolve.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity object or NULL if there is no entity with the given id.
   */
  public function resolve($value, array $args = [], ResolveInfo $info) {
    /** @var \Drupal\Core\Entity\EntityRepository $entityRepository */
    $entityRepository = $this->container->get('entity.repository');
    $entity = $entityRepository->loadEntityByUuid($this->entityType, $args['uuid']);
    $this->addCacheableDependency($entity);
    return $entity;
  }
}
