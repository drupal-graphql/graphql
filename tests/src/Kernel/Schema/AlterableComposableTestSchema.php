<?php

namespace Drupal\Tests\graphql\Kernel\Schema;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\AlterableComposableSchema;

/**
 * GraphQL alterable test schema.
 *
 * @Schema(
 *   id = "alterable_composable_test",
 *   name = "Alterable composable test schema"
 * )
 */
class AlterableComposableTestSchema extends AlterableComposableSchema {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    return new ResolverRegistry();
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtensions() {
    return $this->extensionManager->getExtensions($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    $id = $this->getPluginId();
    $definition = $this->getPluginDefinition();
    $module = $this->moduleHandler->getModule($definition['provider']);
    $path = 'graphql/' . $id . '.graphqls';
    $file = $module->getPath() . '/' . $path;

    if (!file_exists($file)) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'The module "%s" needs to have a schema definition "%s" in its folder for "%s" to be valid.',
          $module->getName(), $path, $definition['class']));
    }

    return file_get_contents($file) ?: NULL;
  }

}
