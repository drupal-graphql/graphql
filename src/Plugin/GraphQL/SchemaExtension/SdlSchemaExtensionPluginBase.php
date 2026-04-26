<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\SchemaExtension;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use GraphQL\Language\Source;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class that can be used for schema extension plugins.
 */
abstract class SdlSchemaExtensionPluginBase extends PluginBase implements SchemaExtensionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * SdlSchemaExtensionPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   In case the file does not exist.
   */
  public function getBaseDefinition(): ?Source {
    return $this->loadDefinitionFile('base');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   In case the file does not exist.
   */
  public function getExtensionDefinition(): ?Source {
    return $this->loadDefinitionFile('extension');
  }

  /**
   * Loads a schema definition file.
   *
   * @param string $type
   *   The type of the definition file to load.
   *
   * @return \GraphQL\Language\Source
   *   The loaded definition file content.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   In case the file does not exist.
   */
  protected function loadDefinitionFile(string $type): Source {
    $id = $this->getPluginId();
    $definition = $this->getPluginDefinition();
    $module = $this->moduleHandler->getModule($definition['provider']);
    $path = 'graphql/' . $id . '.' . $type . '.graphqls';
    $file = $module->getPath() . '/' . $path;

    if (!file_exists($file)) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf('The module "%s" needs to have a schema definition "%s" in its folder for "%s" to be valid.',
          $module->getName(), $path, $definition['class']));
    }

    $contents = file_get_contents($file);
    if ($contents === FALSE) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'Failed to read schema file "%s".',
          $file
        )
      );
    }

    if (trim($contents) === '') {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'Schema file "%s" may not be empty.',
          $file
        )
      );
    }

    return new Source($contents, $file);
  }

}
