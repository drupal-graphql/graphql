<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate a FieldFormatter plugin applicable to all available field types.
 *
 * @deprecated Will be removed before the first stable release.
 *   Raw values all returned by default for all fields user has access to.
 */
class RawValueFormatterDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The base plugin id.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_field.manager'),
      $basePluginId);
  }

  /**
   * AbstractFieldFormatterDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   An entity field manager instance.
   * @param string $basePluginId
   *   The base plugin id.
   */
  public function __construct(
    EntityFieldManagerInterface $entityFieldManager,
    $basePluginId
  ) {
    $this->basePluginId = $basePluginId;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $fieldTypes = [];

    foreach ($this->entityFieldManager->getFieldMap() as $fields) {
      foreach ($fields as $fieldName => $info) {
        $fieldTypes[] = $info['type'];
      }
    }

    $this->derivatives = [
      [
        'id' => 'raw_value',
        'field_types' => array_unique($fieldTypes),
      ] + $basePluginDefinition
    ];

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
