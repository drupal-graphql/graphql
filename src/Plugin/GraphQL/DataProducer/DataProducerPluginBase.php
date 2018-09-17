<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\CacheableDataProducerPluginInterface;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Type\Definition\ResolveInfo;

class DataProducerPluginBase extends PluginBase implements ConfigurablePluginInterface, PluginFormInterface, CacheableDataProducerPluginInterface {

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return mixed
   * @throws \Exception
   */
  public function __invoke($value, $args, ResolveContext $context, ResolveInfo $info) {
    if (!method_exists($this, 'resolve')) {
      throw new \LogicException(sprintf('The plugin %s does not implement a resolve method.', $this->getPluginId()));
    }

    // Allow arguments to be resolved lazily too.
    $values = $this->getConsumedValues($value, $args, $context, $info);
    return DeferredUtility::returnFinally($values, function ($values) use ($context) {
      // TODO: Read the cache entry if the resolver is cacheable.
      if ($this instanceof CacheableDataProducerPluginInterface) {
        array_push($values, $metadata = new CacheableMetadata());
      }

      $output = call_user_func_array([$this, 'resolve'], $values);
      return DeferredUtility::applyFinally($output, function ($value) use ($output, $context, &$metadata) {
        if ($this instanceof CacheableDataProducerPluginInterface && isset($metadata)) {
          $context->addCacheableDependency($metadata);

          if ($value instanceof CacheableDependencyInterface) {
            $context->addCacheableDependency($value);
          }
        }
      });
    });
  }

  /**
   * @param $from
   *
   * @return boolean
   */
  protected function hasInputMapper($from) {
    if (!($this instanceof ConfigurablePluginInterface)) {
      return FALSE;
    }

    return isset($this->getConfiguration()['mapping'][$from]);
  }

  /**
   * @param $from
   *
   * @return callable|null
   */
  protected function getInputMapper($from) {
    return $this->getConfiguration()['mapping'][$from] ?? NULL;
  }

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return array
   * @throws \Exception
   */
  protected function getConsumedValues($value, $args, ResolveContext $context, ResolveInfo $info) {
    $values = [];

    $definitions = $this->getPluginDefinition();
    $consumes = isset($definitions['consumes']) ? $definitions['consumes'] : [];
    foreach ($consumes as $key => $definition) {
      if ($definition->isRequired() && !$this->hasInputMapper($key)) {
        throw new \Exception(sprintf('Missing input data mapper for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }

      $mapper = $this->getInputMapper($key);
      if (isset($mapper) && !is_callable($mapper)) {
        throw new \Exception(sprintf('Invalid input mapper for %s on field %s on type %s. Input mappers need to be callable.', $key, $info->fieldName, $info->parentType->name));
      }

      $values[$key] = isset($mapper) ? $mapper($value, $args, $context, $info) : NULL;
      if ($definition->isRequired() && !isset($values[$key])) {
        throw new \Exception(sprintf('Missing input data for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }
    }

    return $values;
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
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // TODO: Add configuration form for mappings.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Add configuration validation for mappings.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Save mappings.
  }
}
