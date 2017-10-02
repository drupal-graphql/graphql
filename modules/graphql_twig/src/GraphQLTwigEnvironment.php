<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\graphql\QueryProcessor;

class GraphQLTwigEnvironment extends TwigEnvironment {

  /**
   * @var \Drupal\graphql\QueryProcessor
   */
  protected $processor;

  public function __construct($root, CacheBackendInterface $cache, $twig_extension_hash, StateInterface $state, \Twig_LoaderInterface $loader = NULL, array $options = [], QueryProcessor $processor) {
    parent::__construct(
      $root,
      $cache,
      $twig_extension_hash,
      $state,
      $loader,
      $options
    );

    $this->processor = $processor;
  }


  public function loadTemplate($name, $index = NULL) {
    $template = parent::loadTemplate($name, $index);
    if (method_exists($template, 'setGraphQLProcessor')) {
      $template->setGraphQLProcessor($this->processor);
    }
    return $template;
  }

}