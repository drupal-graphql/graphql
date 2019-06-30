<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLParse test class.
 *
 * @group graphql
 */
class XMLParseTest extends XMLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLParse::resolve
   */
  public function testXMLParse() {
    $result = $this->executeDataProducer('xml_parse', [
      'input' => $this->getDocumentSource(),
    ]);

    $this->assertInstanceOf(\DOMElement::class, $result);
  }
}
