<?php

namespace Drupal\graphql\GraphQL\Execution;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
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
    $this->container = $container;

    parent::__construct($schema);
  }

  /**
   * {@inheritdoc}
   */
  protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = NULL) {
    $arguments = $this->parseArgumentsValues($field, $ast);
    $astFields = $ast instanceof AstQuery ? $ast->getFields() : [];
    $resolveInfo = $this->createResolveInfo($field, $astFields);

    if ($field instanceof Field) {
      if (($resolveFunction = $this->getResolveFunction($field)) && is_callable($resolveFunction)) {
        return $resolveFunction($parentValue, $arguments, $resolveInfo);
      }
      else if ($propertyValue = TypeService::getPropertyValue($parentValue, $field->getName())) {
        return $propertyValue;
      }
    }

    if ($field instanceof ContainerAwareInterface) {
      $field->setContainer($this->container);
    }

    return $field->resolve($parentValue, $arguments, $resolveInfo);
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
}
