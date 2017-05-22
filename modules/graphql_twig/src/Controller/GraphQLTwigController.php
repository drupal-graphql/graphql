<?php

namespace Drupal\graphql_twig\Controller;

use Drupal\graphql\GraphQL\Execution\Processor;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render Pages with twig templates and graphql.
 */
class GraphQLTwigController implements ContainerAwareInterface {
  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = NULL) {
    $this->container = $container;
  }

  /**
   * Render a graphql path.
   */
  public function page() {
    $processor = new Processor($this->container, $this->container->get('graphql.schema'));
  }

}
