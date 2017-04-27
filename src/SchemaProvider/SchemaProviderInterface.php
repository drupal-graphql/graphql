<?php

namespace Drupal\graphql\SchemaProvider;

interface SchemaProviderInterface {

  /**
   * @return \Youshido\GraphQL\Schema\AbstractSchema
   */
  public function getSchema();

}
