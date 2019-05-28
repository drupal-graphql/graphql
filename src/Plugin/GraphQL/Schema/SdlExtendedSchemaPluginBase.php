<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Core\Cache\CacheBackendInterface;
use GraphQL\Language\Parser;
use GraphQL\Utils\SchemaExtender;

abstract class SdlExtendedSchemaPluginBase extends SdlSchemaPluginBase {

  /**
   * Retrieves the parsed AST of the extended schema definition.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed extended schema document.
   */
  protected function getExtendedSchemaDocument() {
    // Only use caching of the parsed document if aren't in development mode.
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($this->getPluginId())) {
      return $cache->data;
    }

    $ast = Parser::parse($this->getExtendedSchemaDefinition());
    if (!empty($this->inDevelopment)) {
      $this->astCache->set($this->getPluginId(), $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return SchemaExtender::extend(parent::getSchema(), $this->getExtendedSchemaDocument());
  }

  /**
   * Retrieves the raw extended schema definition string.
   *
   * @return string
   *   The extended schema definition.
   */
  abstract protected function getExtendedSchemaDefinition();

}
