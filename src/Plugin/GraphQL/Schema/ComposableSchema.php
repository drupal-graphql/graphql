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
    }, $this->getConfiguration()['extensions']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return <<<GQL
      type Schema {
        query: Query
      }

      type Query {
        # TODO: Remove placeholder field.
        __placeholder: String
      }
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
    $options = [];
    foreach ($extensions as $key => $extension) {
      $options[$key] = $extension['id'];
    }

    $form['extensions'] = array(
      '#type' => 'checkboxes',
      '#required' => FALSE,
      '#options' => $options,
      '#title' => t('Enabled extensions.'),
      '#default_value' => $this->configuration['extensions'] ?? [],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
//    $form_state->setErrorByName('plugins', 'test validation');
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitConfigurationForm() method.
  }

}
