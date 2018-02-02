<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigEntityPropertyDeriver extends DeriverBase implements ContainerDeriverInterface {

  const TYPEMAP = [
    'string' => 'String',
    'text' => 'String',
    'integer' => 'Int',
    'boolean' => 'Boolean',
  ];

  /**
   * The typed config manager service.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ConfigFieldDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, TypedConfigManagerInterface $typedConfigManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typedConfigManager = $typedConfigManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
      if (!$entityType instanceof ConfigEntityTypeInterface) {
        continue;
      }

      $prefix = $entityType->getConfigPrefix();
      if (!$this->typedConfigManager->hasDefinition("$prefix.*")) {
        continue;
      }

      $definition = $this->typedConfigManager->getDefinition("$prefix.*");
      if (empty($definition['mapping'])) {
        continue;
      }

      $export = array_diff_key($entityType->getPropertiesToExport() ?: [], ['_core' => TRUE]);
      $properties = array_intersect_key($definition['mapping'], $export);
      foreach ($properties as $propertyName => $propertyDefinition) {
        $this->derivatives["$entityTypeId:$propertyName"] = [
          'name' => StringHelper::propCase($propertyName),
          'type' => $this->getType($propertyDefinition),
          'parents' => [StringHelper::camelCase($entityTypeId)],
          'property' => $propertyName,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Derive the type name for a property definition.
   *
   * @param $propertyDefinition
   *   The property definition array.
   *
   * @return string
   *   The type name.
   */
  protected function getType($propertyDefinition) {
    // References to other config schemas cannot be resolved at build-time. To
    // circumvent this problem, we simply return a generic union type shared by
    // all config types.
    if (!$this->typedConfigManager->hasDefinition($propertyDefinition['type'])) {
      return 'Config';
    }

    $dataDefinition = $this->typedConfigManager->buildDataDefinition($propertyDefinition, NULL);
    $dataClass = $dataDefinition->getClass();
    if ($dataClass instanceof PrimitiveInterface) {
      return 'String';
    }

    if ($dataDefinition instanceof ListDataDefinitionInterface) {
      $foo = '';
    }

    if ($dataDefinition instanceof ComplexDataDefinitionInterface) {
      $foo = '';
    }

    $type = $propertyDefinition['type'];
    if (!empty(self::TYPEMAP[$type])) {
      return self::TYPEMAP[$type];
    }

    return 'String';
  }

}