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
     * @param mixed $type
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
     */
    public function resolveRecursive($type);

    /**
     * @param mixed $type
     *
     * @return bool
     */
    public function applies($type);
}
