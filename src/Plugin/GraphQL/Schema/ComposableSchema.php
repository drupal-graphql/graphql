<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\graphql\GraphQL\ResolverRegistry;

/**
 * @Schema(
 *   id = "composable",
 *   name = "Composable schema"
 * )
 */
class ComposableSchema extends SdlSchemaPluginBase implements ConfigurableInterface, PluginFormInterface {

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
    return array_map(function ($id) {
      return $this->extensionManager->createInstance($id);
    }, array_filter($this->getConfiguration()['extensions']));
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return <<<GQL
      type Schema {
        query: Query
      }

      type Query
GQL;

  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $extensions = $this->extensionManager->getDefinitions();

    $form['extensions'] = array(
      '#type' => 'checkboxes',
      '#required' => FALSE,
      '#title' => t('Enabled extensions'),
      '#options' => [],
      '#default_value' => $this->configuration['extensions'] ?? [],
    );

    foreach ($extensions as $key => $extension) {
      $form['extensions']['#options'][$key] = $extension['name'] ?? $extension['id'];

      if (!empty($extension['description'])) {
        $form['extensions'][$key]['#description'] = $extension['description'];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $formState) {
    // TODO: Validate dependencies between extensions.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $formState) {
    // Nothing to do here.
  }

}
