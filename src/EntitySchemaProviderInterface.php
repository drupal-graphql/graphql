<?php

/**
 * @file
 * Contains \Drupal\graphql\EntitySchemaProviderInterface.
 */

namespace Drupal\graphql;

interface EntitySchemaProviderInterface extends SchemaProviderInterface {
    /**
     * @param string $entity_type_id
     *
     * @return mixed
     */
    public function getEntityTypeInterface($entity_type_id);

    /**
     * @param string $entity_type_id
     * @param string $bundle_name
     *
     * @return mixed
     */
    public function getEntityBundleType($entity_type_id, $bundle_name);
}
