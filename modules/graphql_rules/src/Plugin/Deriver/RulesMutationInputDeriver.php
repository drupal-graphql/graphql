<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\graphql_rules\Plugin\GraphQL\Mutations\RulesMutation;
use Drupal\rules\Entity\RulesComponentConfig;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Derive fields from configured views.
 */
class RulesMutationInputDeriver extends RulesMutationDeriverBase {

  /**
   * Returns plugin definition based on given action.
   *
   * @param \Drupal\rules\Entity\RulesComponentConfig $rulesComponent
   *   The rules component.
   * @param array $basePluginDefinition
   *   Base definition.
   *
   * @return array
   */
  protected function getDefinition(RulesComponentConfig $rulesComponent, $basePluginDefinition) {
    $inputFields = [];

    foreach ($rulesComponent->getContextDefinitions() as $contextName => $contextDefinition) {
      $inputFields[$contextName] = [
        'nullable' => !$contextDefinition->isRequired() && $contextDefinition->isAllowedNull(),
        'multi' => $contextDefinition->isMultiple(),
        'type' => '', // TODO Get the type of the input field. Type mapping from graphql_content might turn out handy.
      ];
    }

    $this->derivatives[$rulesComponent->id()] = [
      'name' => RulesMutation::getInputId($rulesComponent),
      'fields' => $inputFields,
    ] + $basePluginDefinition;
  }

}
