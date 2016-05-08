<?php

namespace Drupal\graphql;

interface SchemaProviderInterface {
    /**
     * @return array
     */
    public function getQuerySchema();

    /**
     * @return array
     */
    public function getMutationSchema();
}
