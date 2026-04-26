<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Attribute\Schema;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Language\Source;
use GraphQL\Language\SourceLocation;

/**
 * A schema that is composed of extensions, each adding to the schema.
 */
#[Schema(
  id: "composable",
  name: "Composable schema"
)]
class ComposableSchema extends SdlSchemaPluginBase implements ConfigurableInterface, PluginFormInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function registerResolvers(ResolverRegistryInterface $registry): void {
    // The composable schema provides no resolvers on its own, all of them come
    // from extensions that are configured for the schema.
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtensions(): array {
    $extensions = array_map(function ($id) {
      return $this->extensionManager->createInstance($id);
    }, array_filter($this->getConfiguration()['extensions']));

    // Order the extensions by priority so that higher priority extensions are
    // processed first.
    return $this->extensionManager->sortByPriority($extensions);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition(): Source {
    return new Source(
      <<<GQL
            type Schema {
              query: Query
            }

            type Query
      GQL,
      __FILE__,
      new SourceLocation(__LINE__ - 7, 1),
    );
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
  public function setConfiguration(array $configuration): void {
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

    $form['extensions'] = [
      '#type' => 'checkboxes',
      '#required' => FALSE,
      '#title' => $this->t('Enabled extensions'),
      '#options' => [],
      '#default_value' => $this->configuration['extensions'] ?? [],
    ];

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
  public function validateConfigurationForm(array &$form, FormStateInterface $formState): void {
    // @todo Validate dependencies between extensions.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $formState): void {
    // Nothing to do here.
  }

}
