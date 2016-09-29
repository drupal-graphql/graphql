<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;

use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\TypeInterface;

class EntityByIdField extends FieldBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * The entity type handled by this field instance.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Constructs an EntityByIdField object.
   *
   * @param string $entityType
   *   The entity type handled by this field instance.
   * @param \Youshido\GraphQL\Type\TypeInterface $outputType
   *   The GraphQL type that this field resolves to.
   */
  public function __construct($entityType, TypeInterface $outputType) {
    $this->entityType = $entityType;

    // Generate a human readable name from the entity type.
    $typeName = StringHelper::formatPropertyName($entityType);

    $config = [
      'name' => "${typeName}ById",
      'type' => $outputType,
      'args' => [
        'id' => new NonNullType(new IntType()),
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
    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    $entityStorage = $entityTypeManager->getStorage($this->entityType);

    return $entityStorage->load($args['id']);
  }
}
