<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql\GraphQL\Field\Entity\EntityIdField;
use Drupal\graphql\GraphQL\Field\Entity\EntityTypeField;
use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql\GraphQL\Relay\Type\NodeInterfaceType;
use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql\Utility\StringHelper;

class EntityObjectType extends AbstractObjectType {

  /**
   * Field definitions
   */
  protected $fieldDefinitions;

  /**
   * Creates an EntityObjectType instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition for this object type.
   * @param string $bundle
   *   The entity bundle.
   * @param array $fieldDefinitions
   *   The entity field definitions.
   */
  public function __construct(EntityTypeInterface $entityType, $bundle, $fieldDefinitions) {
    $entityTypeId = $entityType->id();
    $this->fieldDefinitions = $fieldDefinitions;
    $typeName = StringHelper::formatTypeName("$entityTypeId:$bundle");

    $config = [
      'name' => "Entity{$typeName}",
      'interfaces' => [
        new NodeInterfaceType(),
        new EntityInterfaceType(),
        new EntitySpecificInterfaceType($entityType),
      ],
      'fields' => [
        'id' => new GlobalIdField("entity/$entityTypeId"),
        'entityId' => new EntityIdField(),
        'entityType' => new EntityTypeField()
      ] 
    ];

    parent::__construct($config);
  }

  /**
   * {@inheritdoc}
   */
  public function build($config){
    foreach ($this->fieldDefinitions as $fieldDefinition) {
      // Have a better resolver here, this is just for proof of concept. Doesn't even work for types with _ at the moment.
      $className = "Drupal\graphql\GraphQL\Field\Entity\Entity" . ucfirst($fieldDefinition->getType()) . "Field";
      if (class_exists($className)) {
        $config->addfield(new $className($fieldDefinition));
      }
    }
  }
}
