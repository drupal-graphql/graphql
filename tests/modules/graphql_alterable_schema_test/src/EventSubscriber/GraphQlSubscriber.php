<?php

declare(strict_types = 1);

namespace Drupal\graphql_alterable_schema_test\EventSubscriber;

use Drupal\graphql\Event\AlterSchemaDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GraphQlSubscriber.
 *
 * Subscribes to the graphql schema alter events.
 */
class GraphQlSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      AlterSchemaDataEvent::EVENT_NAME => ['alterSchemaData'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterSchemaData(AlterSchemaDataEvent $event): void {
    $schemaData = $event->getSchemaData();
    // It is not recommended direct replacement, better user parsing or regex.
    // But this is an example of what you can do.
    $schemaData[0] = str_replace('alterableQuery(data: AlterableArgument): String', 'alterableQuery(data: AlterableArgument!): String', $schemaData[0]);
    $event->setSchemaData($schemaData);
  }

}
