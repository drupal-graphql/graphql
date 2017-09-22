<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\graphql_rules\Plugin\GraphQL\Mutations\RulesAction;
use Drupal\rules\Core\RulesActionInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Derive fields from configured views.
 */
class RulesActionInputDeriver extends RulesActionDeriverBase {

  /**
   * Returns plugin definition based on given action.
   *
   * @param \Drupal\rules\Core\RulesActionInterface $action
   *   The action plugin.
   * @param array $basePluginDefinition
   *   Base definition.
   *
   * @return array
   */
  protected function getDefinition(RulesActionInterface $action, $basePluginDefinition) {
    $inputFields = [];

    foreach ($action->getContextDefinitions() as $contextName => $contextDefinition) {
      $inputFields[$contextName] = [
        'nullable' => !$contextDefinition->isRequired() && $contextDefinition->isAllowedNull(),
        'multi' => $contextDefinition->isMultiple(),
        'type' => '', // TODO Get the type of the input field. Type mapping from graphql_content might turn out handy.
      ];
    }

    // TODO There doesn't seems to be a generic way to get the result of action execution (a return value).

    $this->derivatives[$action->getPluginId()] = [
      'name' => RulesAction::getInputId($action),
      'fields' => $inputFields,
    ] + $basePluginDefinition;
  }

}
