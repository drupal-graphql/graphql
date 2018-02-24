<?php

namespace Drupal\graphql\GraphQL\Visitors;

use GraphQL\Language\AST\OperationDefinition;
use GraphQL\Validator\ValidationContext;

class ComplexityCalculator {

  /**
   * {@inheritdoc}
   */
  public function calculate(OperationDefinition $definition, ValidationContext $context, \ArrayObject $variables, \ArrayObject $structure) {
    // TODO: Calculate complexity.
  }
}