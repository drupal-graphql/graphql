<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLAttribute test class.
 *
 * @group graphql
 */
class XMLAttributeTest extends XMLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLAttribute::resolve
   */
  public function testXMLAttribute() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'xml_attribute',
      'configuration' => []
    ]);

    $document = $this->loadDocument();
    $xpath = new \DOMXPath($document->ownerDocument);
    $div = iterator_to_array($xpath->query('//div/div', $document));
    $h1 = iterator_to_array($xpath->query('//div/h1', $document));

    $h1_attribute = $plugin->resolve($h1[0], 'data-tag-type');
    $this->assertEquals('some_header', $h1_attribute);
    $h1_attribute = $plugin->resolve($h1[0], 'no-attribute');
    $this->assertEquals('', $h1_attribute);

    $div_attribute = $plugin->resolve($div[0], 'class');
    $this->assertEquals('content', $div_attribute);
  }
}
