<?php

namespace Drupal\graphql_core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Remap artificial requests to subrequest extraction controller.
 */
class EventListener implements EventSubscriberInterface {

  /**
   * Handle kernel request events.
   *
   * If there is a `graphql_subrequest` attribute on the current request,
   * pass the request to the.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->has('graphql_subrequest')) {
      $request->attributes->set('_controller', '\Drupal\graphql_core\SubrequestExtractor:extract');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }

}

