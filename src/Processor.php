<?php

namespace Drupal\graphql;

use Drupal\graphql\Rule\TypeValidationRule;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
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
   * {@inheritdoc}
   */
  protected function resolveFieldValue(AbstractField $field, $contextValue, Query $query) {
    $resolveInfo = new ResolveInfo($field, $query->getFields(), $field->getType(), $this->executionContext);
    $args = $this->parseArgumentsValues($field, $query);

    if ($field instanceof Field) {
      if ($resolveFunc = $field->getConfig()->getResolveFunction()) {
        if (is_array($resolveFunc) && count($resolveFunc) == 2 && strpos($resolveFunc[0], '@') === 0) {
          $service = substr($resolveFunc[0], 1);
          $method = $resolveFunc[1];

          if (!$this->container->has($service)) {
            throw new ResolveException(sprintf('Resolve service "%s" not found for field "%s".', $service, $field->getName()));
          }

          $serviceInstance = $this->container->get($service);

          if (!method_exists($serviceInstance, $method)) {
            throw new ResolveException(sprintf('Resolve method "%s" not found in "%s" service for field "%s".', $method, $service, $field->getName()));
          }

          return $serviceInstance->$method($contextValue, $args, $resolveInfo);
        }

        if (!is_callable($resolveFunc)) {
          throw new ResolveException('Resolve function is not callable.');
        }

        return $resolveFunc($contextValue, $args, $resolveInfo);
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
}