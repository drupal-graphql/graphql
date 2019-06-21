<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Resolver\ResolverInterface;

class ArgumentsResolver {

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The plugin config.
   *
   * @var array
   */
  protected $config;

  /**
   * Construct ArgumentResolver object.
   *
   * @param array $definition
   *   The plugin definition.
   * @param array $config
   *   The plugin config.
   */
  public function __construct($definition, $config) {
    $this->definition = $definition;
    $this->config = $config;
  }

  /**
   * Returns the arguments to pass to the plugin.
   *
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return array Arguments to use.
   * @throws \Exception
   */
  public function getArguments($value, $args, $context, $info) {
    $class = $this->definition['class'];

    // TODO: Use dynamic method name.
    $r = new \ReflectionMethod('\\' . $class, 'resolve');
    $params = $r->getParameters();
    $values = [];

    foreach ($params as $param) {
      $key = $param->getName();

      // Do not process metadata argument.
      if ($key == 'metadata') {
        continue;
      }
      if (!$param->isDefaultValueAvailable() && !$this->hasInputMapper($key)) {
        throw new \Exception(sprintf('Missing input data mapper for argument %s.', $key));
      }
      $mapper = $this->getInputMapper($key);

      if (isset($mapper) && !$mapper instanceof ResolverInterface) {
        throw new \Exception(sprintf('Invalid input mapper for argument %s.', $key));
      }

      // Resolve argument value.
      $values[$key] = isset($mapper) ? $mapper->resolve($value, $args, $context, $info) : NULL;

      if (!$param->isDefaultValueAvailable() && !isset($values[$key])) {
        throw new \Exception(sprintf('Missing input data for argument %s on field %s on type %s.', $key));
      }
    }
    return $values;
  }

  /**
   * @param $from
   *
   * @return boolean
   */
  protected function hasInputMapper($from) {
    return isset($this->config['mapping'][$from]);
  }

  /**
   * @param $from
   *
   * @return callable|null
   */
  protected function getInputMapper($from) {
    return $this->config['mapping'][$from] ?? NULL;
  }
}
