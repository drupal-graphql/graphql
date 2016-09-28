<?php

namespace Drupal\graphql\GraphQL\Validator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\Context\ExecutionContextInterface;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Validator\ResolveValidator\ResolveValidator as BaseResolveValidator;

class ResolveValidator extends BaseResolveValidator {
  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a ResolveValidator object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Youshido\GraphQL\Execution\Context\ExecutionContextInterface $executionContext
   *   The execution context.
   */
  public function __construct(ContainerInterface $container, ExecutionContextInterface $executionContext) {
    $this->container = $container;

    parent::__construct($executionContext);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveAbstractType(AbstractType $type, $resolvedValue) {
    if (in_array('Symfony\Component\DependencyInjection\ContainerAwareInterface', class_implements($type))) {
      $type->setContainer($this->container);
    }

    return parent::resolveAbstractType($type, $resolvedValue);
  }
}