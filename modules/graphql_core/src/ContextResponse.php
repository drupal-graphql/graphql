<?php

namespace Drupal\graphql_core;

use Drupal\Component\Plugin\Context\ContextInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kernel response containing context objects.
 */
class ContextResponse extends Response {

  /**
   * The retrieved context.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface
   */
  protected $context;

  /**
   * Set the context value.
   *
   * @param \Drupal\Component\Plugin\Context\ContextInterface $context
   *   The context object.
   */
  public function setContext(ContextInterface $context) {
    $this->context = $context;
  }

  /**
   * Retrieve the context object.
   *
   * @return \Drupal\Component\Plugin\Context\ContextInterface
   *   The contained context object.
   */
  public function getContext() {
    return $this->context;
  }

}
