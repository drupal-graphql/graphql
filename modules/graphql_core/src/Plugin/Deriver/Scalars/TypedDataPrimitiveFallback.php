<?php

namespace Drupal\graphql_core\Plugin\Deriver\Scalars;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TypedDataPrimitiveFallback extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static($container->get('typed_data_manager'));
  }

  /**
   * TypedDataPrimitiveFallback constructor.
   *
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager.
   */
  public function __construct(TypedDataManagerInterface $typedDataManager) {
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    // Add a derivative for the actual "any" type.
    $this->derivatives['any'] = $basePluginDefinition;

    foreach ($this->typedDataManager->getDefinitions() as $typeName => $typeDefinition) {
      if (in_array('Drupal\Core\TypedData\PrimitiveInterface', class_implements($typeDefinition['class']))) {
        $this->derivatives[$typeName] = [
          'name' => StringHelper::camelCase($typeName),
          'description' => !empty($typeDefinition['description']) ? $typeDefinition['description'] : '',
          'provider' => isset($typeDefinition['provider']) ? $typeDefinition['provider'] : null,
          'weight' => -10,
          'type' => $typeName,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
