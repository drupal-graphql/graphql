<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

/**
 * Data producers XMLXpath test class.
 *
 * @group graphql
 */
class XMLXpathTest extends XMLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLXpath::resolve
   */
  public function testXMLXpath() {
    $document = $this->loadDocument();

    $result = $this->executeDataProducer('xml_xpath', [
      'dom' => $document,
      'query' => '//div/h1',
    ]);

    $this->assertEquals(1, count($result));
    $this->assertEquals('h1', $result[0]->tagName);

    $result = $this->executeDataProducer('xml_xpath', [
      'dom' => $document,
      'query' => '//div/div/div',
    ]);

    $this->assertEquals(3, count($result));
    $this->assertEquals('div', $result[0]->tagName);
    $this->assertEquals('div', $result[1]->tagName);
    $this->assertEquals('div', $result[2]->tagName);

    // Test that the resolve can accept a DOMElement object too, not only a
    // document root.
    $element = $this->executeDataProducer('xml_xpath', [
      'dom' => $document,
      'query' => '//div/div/span',
    ]);

    $result = $this->executeDataProducer('xml_xpath', [
      'dom' => $element[0],
      'query' => './p',
    ]);

    $this->assertEquals(2, count($result));
    $this->assertEquals('p', $result[0]->tagName);
    $this->assertEquals('p', $result[1]->tagName);

    // Test for non-existent element.
    $result = $this->executeDataProducer('xml_xpath', [
      'dom' => $document,
      'query' => '//div/h2',
    ]);

    $this->assertSame(0, count($result));
  }
}
