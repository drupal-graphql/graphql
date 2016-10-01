<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\graphql\GraphQL\Validator\ResolveValidator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\TypeService;
use Youshido\GraphQL\Validator\Exception\ResolveException;

class Processor extends BaseProcessor {
  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a Processor object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The GraphQL schema.
   */
  public function __construct(ContainerInterface $container, AbstractSchema $schema) {
    parent::__construct($schema);

    $this->container = $container;
    $this->resolveValidator = new ResolveValidator($container, $this->executionContext);
  }

  /**
   * Helper function to resolve a value using a service.
   *
   * @param \Youshido\GraphQL\Field\AbstractField $field
   *
   * @return mixed
   *
   * @throws \Youshido\GraphQL\Validator\Exception\ResolveException
   */
  protected function getResolveFunction(AbstractField $field) {
    if ($resolveFunction = $field->getConfig()->getResolveFunction()) {
      if (is_array($resolveFunction) && !is_callable($resolveFunction) && count($resolveFunction) === 2 && strpos($resolveFunction[0], '@') === 0) {
        $service = substr($resolveFunction[0], 1);
        $method = $resolveFunction[1];

        if (!$this->container->has($service)) {
          throw new ResolveException(sprintf('Resolve service "%s" not found for field "%s".', $service, $field->getName()));
        }

        $serviceInstance = $this->container->get($service);
        if (!method_exists($serviceInstance, $method)) {
          throw new ResolveException(sprintf('Resolve method "%s" not found in "%s" service for field "%s".', $method, $service, $field->getName()));
        }

        return [$serviceInstance, $method];
      }
    }

    return $resolveFunction;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveFieldValue(FieldInterface $field, $contextValue, array $fields, array $args) {
    $resolveInfo = $this->createResolveInfo($field, $fields);

    if ($field instanceof Field) {
      if (($resolveFunction = $this->getResolveFunction($field)) && is_callable($resolveFunction)) {
        return $resolveFunction($contextValue, $args, $resolveInfo);
      }
      else if ($propertyValue = TypeService::getPropertyValue($contextValue, $field->getName())) {
        return $propertyValue;
      }
    }
    else {
      if ($field instanceof ContainerAwareInterface) {
        $field->setContainer($this->container);
      }

      return $field->resolve($contextValue, $args, $resolveInfo);
    }

    return NULL;
  }
}
