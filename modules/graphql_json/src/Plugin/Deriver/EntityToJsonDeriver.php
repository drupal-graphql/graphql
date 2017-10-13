<?php

namespace Drupal\graphql_json\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Only add the field if the serializer module is enabled.
 */
class EntityToJsonDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   *  The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Creates a EntityToJsonDeriver object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    if ($this->moduleHandler->moduleExists('serialization')) {
      $this->derivatives['entity_to_json'] = $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
