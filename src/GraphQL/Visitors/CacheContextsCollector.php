<?php

namespace Drupal\graphql\GraphQL\Visitors;

use Drupal\Core\Cache\Cache;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\LeafType;
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
            $contexts = Cache::mergeContexts($contexts, $this->collectCacheContexts($definition->config['contexts']));
          }

          $parent = $info->getParentType();
          if (!empty($parent->config['contexts'])) {
            $contexts = Cache::mergeContexts($contexts, $this->collectCacheContexts($parent->config['contexts']));
          }

          $type = $info->getType();
          // Collect cache metadata from leaf types.
          if ($type instanceof LeafType && !empty($type->config['contexts'])) {
            $contexts = Cache::mergeContexts($contexts, $this->collectCacheContexts($type->config['contexts']));
          }
        },
      ],
    ];
  }

  /**
   * Collects the cache contexts from a type or field config.
   *
   * @param array|callable $contexts
   *   The cache contexts array or a callable to return cache contexts.
   *
   * @return array
   *   The collected cache contexts.
   */
  protected function collectCacheContexts($contexts) {
    if (is_callable($contexts)) {
      return $contexts();
    }

    return $contexts;
  }
}