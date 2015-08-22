<?php

namespace Drupal\graphql;

interface SchemaProviderInterface {
    /**
     * @return \Fubhy\GraphQL\Schema
     */
    public function getSchema();

    /**
     * @return array
     */
    public function getQuerySchema();

    /**
     * @return array
     */
    public function getMutationSchema();
}
