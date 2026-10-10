<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Entity loading, translation, and rendering data producer tests.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class EntityLoadTest extends EntityTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslation::resolve
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslations::resolve
   */
  public function testResolveTranslation(): void {
    $french = $this->executeDataProducer('entity_translation', [
      'entity' => $this->node,
      'language' => 'fr',
    ]);

    $this->assertSame('sit amet fr', $french->label());

    $translations = $this->executeDataProducer('entity_translations', [
      'entity' => $this->node,
    ]);

    $this->assertSame('Dolor', $translations['en']->label());
    $this->assertSame('sit amet fr', $translations['fr']->label());
    $this->assertSame('sit amet de', $translations['de']->label());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityLoad(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
    ]);

    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoadByUuid::resolve
   */
  public function testResolveEntityLoadByUuid(): void {
    $result = $this->executeDataProducer('entity_load_by_uuid', [
      'type' => $this->node->getEntityTypeId(),
      'uuid' => $this->node->uuid(),
    ]);

    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveUnknownEntityLoad(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => 0,
    ]);

    $this->assertNull($result);
    $this->assertContains('node_list', $this->fieldContext->getCacheTags());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveMismatchEntityLoad(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
      // cspell:ignore otherbundle
      'bundles' => ['otherbundle'],
    ]);

    $this->assertNull($result);
    $this->assertContains('node:1', $this->fieldContext->getCacheTags());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveTranslatedEntityLoad(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
      'language' => 'fr',
    ]);

    $this->assertSame('fr', $result->language()->getId());
    $this->assertSame('sit amet fr', $result->getTitle());
  }

  /**
   * Make sure that passing a NULL id does not produce any warnings.
   */
  public function testResolveEntityLoadWithNullId(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => NULL,
    ]);

    $this->assertNull($result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityRendered(): void {
    // Drupal core's Twig escape filter currently fails during entity rendering.
    // @see https://www.drupal.org/project/drupal/issues/3625969
    $this->markTestSkipped('Blocked by Drupal core issue #3625969.');
    // @phpstan-ignore deadCode.unreachable
    $result = $this->executeDataProducer('entity_rendered', [
      'entity' => $this->node,
      'mode' => 'default',
    ]);

    $this->assertMatchesRegularExpression('#<a href="/node/1" rel="bookmark">\s*<span>' . $this->node->getTitle() . '</span>#', $result);
    $this->assertContains('node:1', $this->fieldContext->getCacheTags());
  }

}
