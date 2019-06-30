<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLContent test class.
 *
 * @group graphql
 */
class XMLContentTest extends XMLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLContent::resolve
   */
  public function testXMLContent() {
    $document = $this->loadDocument();
    $xpath = new \DOMXPath($document->ownerDocument);
    $h1 = iterator_to_array($xpath->query('//div/h1', $document));
    $span = iterator_to_array($xpath->query('//div/div/span', $document));

    $this->assertEquals('Header', $this->executeDataProducer('xml_content', [
      'dom' => $h1[0],
    ]));

    $content = '<p>This is one paragraph.</p><p>This is a second paragraph.</p>';
    $this->assertEquals($content, $this->executeDataProducer('xml_content', [
      'dom' => $span[0],
    ]));
  }
}
