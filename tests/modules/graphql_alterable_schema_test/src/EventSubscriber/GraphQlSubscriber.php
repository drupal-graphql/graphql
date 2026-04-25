<?php

declare(strict_types=1);

namespace Drupal\graphql_alterable_schema_test\EventSubscriber;

use Drupal\graphql\Event\AlterSchemaDataEvent;
use Drupal\graphql\Event\AlterSchemaExtensionDataEvent;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
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
      AlterSchemaExtensionDataEvent::EVENT_NAME => ['alterSchemaExtensionData'],
      AlterSchemaDataEvent::EVENT_NAME => ['alterSchemaData'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterSchemaExtensionData(AlterSchemaExtensionDataEvent $event): void {
    $schemaData = $event->getSchemaExtensionData();

    // Test empty extensions can still extend the schema.
    // https://github.com/drupal-graphql/graphql/issues/1395
    if (empty($schemaData['graphql_alterable_schema_test'])) {
      $schemaData['graphql_alterable_schema_test'] = Parser::parse(<<<GQL
        extend type Result {
          empty: Boolean!
        }
      GQL);
    }
    // Test regular schema alteration using an AST visitor.
    else {
      Visitor::visit($schemaData['graphql_alterable_schema_test'], [
        NodeKind::FIELD_DEFINITION => [
          'enter' => function (Node $node) {
            assert($node instanceof FieldDefinitionNode);
            if ($node->name->value !== 'position') {
              // Do nothing.
              return NULL;
            }
            // Make the type non-nullable.
            $node->type = new NonNullTypeNode(['type' => $node->type]);

            return $node;
          },
          'leave' => function (Node $node) {
            assert($node instanceof FieldDefinitionNode);
            // Once we've visited the position field node we can stop.
            if ($node->name->value === 'position') {
              return Visitor::stop();
            }
            // Do nothing.
            return NULL;
          },
        ],
      ]);
    }

    $event->setSchemaExtensionData($schemaData);
  }

  /**
   * {@inheritdoc}
   */
  public function alterSchemaData(AlterSchemaDataEvent $event): void {
    $schemaData = $event->getSchemaData();

    Visitor::visit($schemaData['test'], [
      NodeKind::INPUT_VALUE_DEFINITION => [
        'enter' => function (Node $node) {
          assert($node instanceof InputValueDefinitionNode);
          if ($node->name->value !== 'id') {
            // Do nothing.
            return NULL;
          }
          // Make the type non-nullable.
          $node->type = new NonNullTypeNode(['type' => $node->type]);

          return $node;
        },
        'leave' => function (Node $node) {
          assert($node instanceof InputValueDefinitionNode);
          // Once we've visited the id argument node we can stop.
          if ($node->name->value === 'id') {
            return Visitor::stop();
          }
          // Do nothing.
          return NULL;
        },
      ],
    ]);

    $event->setSchemaData($schemaData);
  }

}
