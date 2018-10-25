<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLParse test class.
 *
 * @group graphql
 */
class XMLParseTest extends XMLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLParse::resolve
   */
  public function testXMLParse() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'xml_parse',
      'configuration' => []
    ]);

    $result = $plugin->resolve($this->getDocumentSource());
    $this->assertInstanceOf(\DOMElement::class, $result);
  }
}
