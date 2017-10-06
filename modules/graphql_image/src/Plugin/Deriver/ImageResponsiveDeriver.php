<?php

namespace Drupal\graphql_image\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Only add the 'responsive' field if the responsive_image module is enabled.
 */
class ImageResponsiveDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Entity type manager.
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
   * EntityBundleDeriver constructor.
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
    /**
     * Add the 'responsive' field only if responsive image module is enabled
     * and there is at least one style defined in the system.
     *
     * @see \Drupal\graphql_image\Plugin\GraphQL\Enums\ResponsiveImageStyleId
     */
    if ($this->moduleHandler->moduleExists('responsive_image')) {
      $imageStyles = ResponsiveImageStyle::loadMultiple();
      if (!empty($imageStyles)) {
        $this->derivatives['responsive_image'] = $basePluginDefinition;
      }
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
