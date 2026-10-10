<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Data producers XMLAttribute test class.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class XMLAttributeTest extends XMLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLAttribute::resolve
   */
  public function testXmlAttribute(): void {
    $document = $this->loadDocument();
    $xpath = new \DOMXPath($document->ownerDocument);
    $div = iterator_to_array($xpath->query('//div/div', $document));
    $h1 = iterator_to_array($xpath->query('//div/h1', $document));

    $this->assertSame('some_header', $this->executeDataProducer('xml_attribute', [
      'dom' => $h1[0],
      'name' => 'data-tag-type',
    ]));

    $this->assertSame('', $this->executeDataProducer('xml_attribute', [
      'dom' => $h1[0],
      'name' => 'no-attribute',
    ]));

    $this->assertSame('content', $this->executeDataProducer('xml_attribute', [
      'dom' => $div[0],
      'name' => 'class',
    ]));
  }

}
