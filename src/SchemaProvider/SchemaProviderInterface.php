<?php

namespace Drupal\graphql\SchemaProvider;

interface SchemaProviderInterface {
  /**
   * @return array
   */
  public function getCacheTags();

  /**
   * @return array
   */
  public function getQuerySchema();

  /**
   * @return array
   */
  public function getMutationSchema();
}
