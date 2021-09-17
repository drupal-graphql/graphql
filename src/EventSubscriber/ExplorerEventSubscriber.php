<?php

namespace Drupal\graphql\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExplorerEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['bypassValidation'];
    return $events;
  }

  /**
   * Add the bypass_validation url parameter to graphql admin routes.
   *
   * @param RequestEvent $event
   */
  public function bypassValidation(RequestEvent $event) {
    $route = \Drupal::routeMatch()->getRouteName();

    // If a bypass_validation param is already set, skip.
    if ($event->getRequest()->get('bypass_validation')) {
      return;
    }

    // Only bypass validation for these two routes.
    if ($route === 'graphql.explorer' || $route === 'graphql.voyager') {
      /** @var \Drupal\graphql\Entity\Server $server */
      $server = $event->getRequest()->get('graphql_server');

      $url = $event->getRequest()->getUri();

      // Get the bypass_validation_token from the server settings.
      $bypass_validation_token = $server->get('bypass_validation_token');

      // Add the bypass_validation parameter to the current url.
      $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'bypass_validation=' . $bypass_validation_token;

      // Redirect to the new url.
      $event->setResponse(new RedirectResponse($url));
    }
  }
}
