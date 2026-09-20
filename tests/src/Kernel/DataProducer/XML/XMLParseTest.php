<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\XML;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Data producers XMLParse test class.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class XMLParseTest extends XMLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\XMLParse::resolve
   */
  public function testXmlParse(): void {
    $result = $this->executeDataProducer('xml_parse', [
      'input' => $this->getDocumentSource(),
    ]);

    $this->assertInstanceOf(\DOMElement::class, $result);
  }

}
