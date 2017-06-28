<?php

namespace Drupal\graphql_breadcrumbs;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Remap artificial requests to block extraction controller.
 */
class EventListener implements EventSubscriberInterface {

  /**
   * Handle kernel request events.
   *
   * If there is a `graphql_breadcrumbs` attribute on the current request, pass the
   * request to a breadcrumbs extraction.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->has('graphql_breadcrumbs')) {
      $request->attributes->set('_controller', '\Drupal\graphql_breadcrumbs\BreadcrumbsExtractor:extract');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }

}

