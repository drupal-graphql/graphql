<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\Plugin\DataProducerPluginManager;

/**
 * Data producers proxy class.
 */
class DataProducerProxy implements ResolverInterface {

  use DependencySerializationTrait;

  /**
   * Plugin manager.
   *
   * @var \Drupal\graphql\Plugin\DataProducerPluginManager
   */
  protected $manager;

  /**
   * The plugin config.
   *
   * @var array
   */
  protected $config;

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $id;

  /**
   * Construct DataProducerProxy object.
   *
   * @param string $id
   *   DataProducer plugin id.
   * @param array $config
   *   Plugin configuration.
   * @param \Drupal\graphql\Plugin\DataProducerPluginManager $manager
   *   DataProducer manager.
   */
  public function __construct($id, array $config, DataProducerPluginManager $manager) {
    $this->id = $id;
    $this->config = $config;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $values = DeferredUtility::waitAll($this->getArguments($value, $args, $context, $info));
    return DeferredUtility::returnFinally($values, function ($values) use ($context, $info) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheContexts(['user.permissions']);

      $executor = new FieldExecutor($this->id, $this->config, $this->manager);
      $output = $executor->resolve($values, $context, $info, $metadata);

      return DeferredUtility::applyFinally($output, function () use ($context, $metadata) {
        $context->addCacheableDependency($metadata);
      });
    });
  }

  /**
   * Returns the arguments to pass to the plugin.
   *
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return array
   *   Arguments to use.
   *
   * @throws \Exception
   */
  protected function getArguments($value, $args, ResolveContext $context, ResolveInfo $info) {
    $argumentResolver = new ArgumentsResolver($this->manager->getDefinition($this->id), $this->config);
    return $argumentResolver->getArguments($value, $args, $context, $info);
  }

}
