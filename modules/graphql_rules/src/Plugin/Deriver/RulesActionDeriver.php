<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\graphql_rules\Plugin\GraphQL\Mutations\RulesAction;
use Drupal\rules\Core\RulesActionInterface;

/**
 * Derive fields from configured rules actions.
 */
class RulesActionDeriver extends RulesActionDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition(RulesActionInterface $action, $basePluginDefinition) {
    $this->derivatives[$action->getPluginId()] = [
      'name' => RulesAction::getId($action),
      'arguments' => [
        'input' => [
          'type' => RulesAction::getInputId($action),
          'nullable' => FALSE,
          'multi' => FALSE,
        ],
      ],
      'action_id' => $action->getPluginId(),
    ] + $basePluginDefinition;
  }

}
