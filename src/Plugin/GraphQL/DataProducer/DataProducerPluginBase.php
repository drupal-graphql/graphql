<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\graphql\Plugin\DataProducerPluginInterface;

class DataProducerPluginBase extends PluginBase implements ConfigurablePluginInterface, PluginFormInterface, DataProducerPluginInterface {
  use DataProducerTrait;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // TODO: Add configuration form for mappings.
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Add configuration validation for mappings.
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Save mappings.
  }
}
