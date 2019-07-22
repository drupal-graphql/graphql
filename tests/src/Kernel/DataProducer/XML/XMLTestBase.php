<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Base class for XML data producer tests.
 *
 * @group graphql
 */
class XMLTestBase extends GraphQLTestBase {

  /**
   * Loads a test document.
   */
  public function loadDocument() {
    $document = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $document->loadHTMLFile(drupal_get_path('module', 'graphql') . '/tests/files/xml/test.xml');
    return $document->documentElement;
  }

  /**
   * Returns the source of the test document.
   * @return bool|string
   */
  public function getDocumentSource() {
    return file_get_contents(drupal_get_path('module', 'graphql') . '/tests/files/xml/test.xml');
  }
}
