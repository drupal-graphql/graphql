<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolverInterface.
 */

namespace Drupal\graphql;

/**
 * Provides a common interface for type resolvers.
 */
interface TypeResolverInterface {
    /**
     * @param mixed $definition
     * @param bool $defer
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
     */
    public function resolveRecursive($definition, $defer = TRUE);

    /**
     * @param mixed $type
     *
     * @return bool
     */
    public function applies($type);
}
