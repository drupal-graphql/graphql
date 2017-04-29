<?php

namespace Drupal\graphql_core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Service using HTTP kernel to extract Drupal context objects.
 *
 * Replaces the controller of requests containing the "graphql_context"
 * attribute with itself and returns a context response instead that will be
 * use as field value for graphql context fields.
 */
class ContextExtractor implements EventSubscriberInterface {

  /**
   * Handle kernel request events.
   *
   * If there is a `graphql_context` attribute on the current request, pass the
   * request to a context extraction.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->has('graphql_context')) {
      $request->attributes->set('_controller', '\Drupal\graphql_core\ContextExtractor:extract');
    }
  }

  /**
   * Extract the required context and return it.
   *
   * @return \Drupal\graphql_core\ContextResponse
   *   A context response instance.
   */
  public function extract() {
    $context_id = \Drupal::request()->attributes->get('graphql_context');
    $response = new ContextResponse();
    $response->setContext(\Drupal::service('graphql.context_repository')->getRuntimeContexts([$context_id])[$context_id]);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }

}
