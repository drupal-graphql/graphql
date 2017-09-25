<?php

namespace Drupal\graphql_rules\Plugin\Deriver;

use Drupal\graphql_rules\Plugin\GraphQL\Mutations\RulesMutation;
use Drupal\rules\Entity\RulesComponentConfig;

/**
 * Derive fields from configured rules actions.
 */
class RulesMutationDeriver extends RulesMutationDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition(RulesComponentConfig $rulesComponent, $basePluginDefinition) {
    $this->derivatives[$rulesComponent->id()] = [
      'name' => RulesMutation::getId($rulesComponent),
      'arguments' => [
        'input' => [
          'type' => RulesMutation::getInputId($rulesComponent),
          'nullable' => FALSE,
          'multi' => FALSE,
        ],
      ],
      'action_id' => $rulesComponent->id(),
    ] + $basePluginDefinition;
  }

}
