<?php

namespace Drupal\graphql\GraphQL\Visitors;

use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\AST\VariableDefinition;
use GraphQL\Validator\Rules\AbstractQuerySecurity;
use GraphQL\Validator\ValidationContext;

class QueryEdgeCollector extends AbstractQuerySecurity {

  /**
   * @var array
   */
  private $calculators;

  /**
   * QueryEdgeCollector constructor.
   *
   * @param array $calculators
   */
  public function __construct(array $calculators) {
    $this->calculators = $calculators;
  }

  /**
   * {@inheritdoc}
   */
  protected function isEnabled() {
    return !empty($this->calculators);
  }

  /**
   * {@inheritdoc}
   */
  public function getVisitor(ValidationContext $context) {
    $variables = new \ArrayObject();
    $structure = new \ArrayObject();

    return $this->invokeIfNeeded($context, [
      NodeKind::SELECTION_SET => function (SelectionSetNode $set) use ($context, &$structure) {
        $parent = $context->getParentType();
        $structure = $this->collectFieldASTsAndDefs($context, $parent, $set, NULL, $structure);
      },
      NodeKind::VARIABLE_DEFINITION => function (VariableDefinition $definition) use ($variables) {
        $variables->append($definition);
      },
      NodeKind::OPERATION_DEFINITION => [
        'leave' => function (OperationDefinitionNode $definition) use ($context, $variables, $structure) {
          foreach ($this->calculators as $visitor) {
            $visitor->calculate($definition, $context, $variables, $structure);
          }
        },
      ],
    ]);
  }
}