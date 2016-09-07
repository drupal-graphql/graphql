<?php

namespace Drupal\graphql\GraphQL\Execution;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Parser\Ast\Field as FieldAst;
use Youshido\GraphQL\Parser\Ast\Query;
use Youshido\GraphQL\Type\TypeService;
use Youshido\GraphQL\Validator\Exception\ResolveException;

class Processor extends BaseProcessor implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = NULL) {
    $this->container = $container;
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
  protected function resolveFieldValue(AbstractField $field, $contextValue, Query $query) {
    $resolveInfo = new ResolveInfo($field, $query->getFields(), $this->executionContext);
    $args = $this->parseArgumentsValues($field, $query);

    if ($field instanceof Field) {
      if (($resolveFunction = $this->getResolveFunction($field)) && is_callable($resolveFunction)) {
        return $resolveFunction($contextValue, $this->parseArgumentsValues($field, $query), $resolveInfo);
      }
      else if ($propertyValue = TypeService::getPropertyValue($contextValue, $field->getName())) {
        return $propertyValue;
      }
    }
    else {
      if (in_array('Symfony\Component\DependencyInjection\ContainerAwareInterface', class_implements($field))) {
        $field->setContainer($this->container);
      }

      return $field->resolve($contextValue, $args, $resolveInfo);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPreResolvedValue($contextValue, FieldAst $fieldAst, AbstractField $field) {
    if ($resolveFunction = $this->getResolveFunction($field)) {
      $resolveInfo = new ResolveInfo($field, [$fieldAst], $this->executionContext);

      if (!$this->resolveValidator->validateArguments($field, $fieldAst, $this->executionContext->getRequest())) {
        throw new \Exception(sprintf('Not valid arguments for the field "%s"', $fieldAst->getName()));
      }

      return $resolveFunction($contextValue, $fieldAst->getKeyValueArguments(), $resolveInfo);
    }

    return parent::getPreResolvedValue($contextValue, $fieldAst, $field);
  }
}