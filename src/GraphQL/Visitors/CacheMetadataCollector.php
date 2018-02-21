<?php

namespace Drupal\graphql\GraphQL\Visitors;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Visitor;
use GraphQL\Validator\Rules\AbstractQuerySecurity;
use GraphQL\Validator\ValidationContext;

class CacheMetadataCollector extends AbstractQuerySecurity {

  /**
   * @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   */
  protected $metadata;

  /**
   * @var array
   */
  protected $variables;

  /**
   * CacheMetadataCollector constructor.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   * @param array|null $variables
   */
  public function __construct(RefinableCacheableDependencyInterface $metadata, array $variables = NULL) {
    $this->metadata = $metadata;
    $this->variables = $variables;
  }

  /**
   * {@inheritdoc}
   */
  protected function isEnabled() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisitor(ValidationContext $context) {
    $this->variables = new \ArrayObject();
    $this->structure = new \ArrayObject();

    return $this->invokeIfNeeded($context, [
      NodeKind::SELECTION_SET => function (SelectionSetNode $selectionSet) use ($context) {
        $this->structure = $this->collectFieldASTsAndDefs(
          $context,
          $context->getParentType(),
          $selectionSet,
          NULL,
          $this->structure
        );
      },
      NodeKind::VARIABLE_DEFINITION => function ($definition, &$variables) {
        array_push( $this->variables, $definition);
        return Visitor::skipNode();
      },
//      NodeKind::OPERATION_DEFINITION => [
//        'leave' => function () {
//          $foo = '';
//        },
//      ],
    ]);
  }
}