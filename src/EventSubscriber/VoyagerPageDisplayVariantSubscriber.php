<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Disables any display variant on the voyager page.
 */
class VoyagerPageDisplayVariantSubscriber implements EventSubscriberInterface {

  /**
   * Disables any display variant on the voyager page.
   *
   * @param \Drupal\Core\Render\PageDisplayVariantSelectionEvent $event
   *   The event to process.
   */
  public function onSelectPageDisplayVariant(PageDisplayVariantSelectionEvent $event) {
    if (strpos($event->getRouteMatch()->getRouteName(), 'graphql.voyager.') === 0) {
      $event->setPluginId(NULL)->stopPropagation();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      RenderEvents::SELECT_PAGE_DISPLAY_VARIANT => [['onSelectPageDisplayVariant']],
    ];

    return $events;
  }
}
