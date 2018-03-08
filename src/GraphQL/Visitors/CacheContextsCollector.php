<?php

namespace Drupal\graphql\GraphQL\Visitors;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Utils\TypeInfo;

class CacheContextsCollector {

  /**
   * {@inheritdoc}
   */
  public function getVisitor(TypeInfo $info, array &$contexts) {
    return [
      NodeKind::FIELD => [
        'leave' => function (FieldNode $field) use ($info, &$contexts) {
          $definition = $info->getFieldDef();
          if (!empty($definition->config['contexts'])) {
            $contexts = array_unique(array_merge($contexts, $definition->config['contexts']));
          }
        },
      ],
    ];
  }
}