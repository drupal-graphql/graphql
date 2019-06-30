<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLAttribute test class.
 *
 * @group graphql
 */
class XMLAttributeTest extends XMLTestBase {

 /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLAttribute::resolve
   */
  public function testXMLAttribute() {
    $document = $this->loadDocument();
    $xpath = new \DOMXPath($document->ownerDocument);
    $div = iterator_to_array($xpath->query('//div/div', $document));
    $h1 = iterator_to_array($xpath->query('//div/h1', $document));

    $this->assertEquals('some_header', $this->executeDataProducer('xml_attribute', [
      'dom' => $h1[0],
      'name' => 'data-tag-type'
    ]));

    $this->assertEquals('', $this->executeDataProducer('xml_attribute', [
      'dom' => $h1[0],
      'name' => 'no-attribute'
    ]));

    $this->assertEquals('content', $this->executeDataProducer('xml_attribute', [
      'dom' => $div[0],
      'name' => 'class'
    ]));
  }
}
