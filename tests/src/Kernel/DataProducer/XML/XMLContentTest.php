<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLContent test class.
 *
 * @group graphql
 */
class XMLContentTest extends XMLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLContent::resolve
   */
  public function testXMLContent() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'xml_content',
      'configuration' => []
    ]);

    $document = $this->loadDocument();
    $xpath = new \DOMXPath($document->ownerDocument);
    $h1 = iterator_to_array($xpath->query('//div/h1', $document));
    $span = iterator_to_array($xpath->query('//div/div/span', $document));

    $h1_output = $plugin->resolve($h1[0]);
    $this->assertEquals('Header', $h1_output);

    $span_output = $plugin->resolve($span[0]);
    $this->assertEquals('<p>This is one paragraph.</p><p>This is a second paragraph.</p>', $span_output);
  }
}
