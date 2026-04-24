<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\Field;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests entity_reference_revisions resolution for node preview.
 *
 * @group graphql
 */
#[RunTestsInSeparateProcesses]
class EntityReferenceRevisionsTest extends GraphQLTestBase {

  /**
   * Referenced nodes.
   *
   * @var array<\Drupal\node\Entity\Node>
   */
  protected array $referencedNodes = [];

  /**
   * Host node simulating preview (unsaved).
   */
  protected Node $hostNode;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_reference_revisions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create content types.
    NodeType::create(['type' => 'test1', 'name' => 'Test1'])->save();
    NodeType::create(['type' => 'test2', 'name' => 'Test2'])->save();

    // Create ERR field storage.
    if (!FieldStorageConfig::loadByName('node', 'field_ref1')) {
      FieldStorageConfig::create([
        'field_name' => 'field_ref1',
        'type' => 'entity_reference_revisions',
        'entity_type' => 'node',
        'cardinality' => -1,
        'settings' => ['target_type' => 'node'],
      ])->save();
    }

    // Attach field to test1 bundle.
    if (!FieldConfig::loadByName('node', 'test1', 'field_ref1')) {
      FieldConfig::create([
        'field_name' => 'field_ref1',
        'entity_type' => 'node',
        'bundle' => 'test1',
        'label' => 'Reference',
        'settings' => ['handler' => 'default', 'handler_settings' => []],
      ])->save();
    }

    // Create referenced nodes (saved, normal revisions).
    $titles = ['Ref1', 'Ref2'];
    foreach ($titles as $title) {
      $node = Node::create(['title' => $title, 'type' => 'test2']);
      $node->save();
      $this->referencedNodes[] = $node;
    }

    // Create unsaved host node (node preview) referencing nodes in a specific
    // order.
    $this->hostNode = Node::create(['title' => 'Host preview', 'type' => 'test1']);

    // Assign field items in memory with explicit revision IDs
    // (simulate preview).
    $this->hostNode->set('field_ref1', [
      [
        'target_id' => $this->referencedNodes[1]->id(),
        'target_revision_id' => $this->referencedNodes[1]->getRevisionId(),
      ],
      [
        'target_id' => $this->referencedNodes[0]->id(),
        'target_revision_id' => $this->referencedNodes[0]->getRevisionId(),
      ],
    ]);
  }

  /**
   * Tests that entity_reference_revisions preserves revision and delta order.
   *
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Field\EntityReferenceRevisions::resolve
   */
  public function testPreviewRevisionMergeOrder(): void {
    $result = $this->executeDataProducer('entity_reference_revisions', [
      'entity' => $this->hostNode,
      'field' => 'field_ref1',
      'access' => TRUE,
      'access_operation' => 'view',
    ]);

    // Ensure field delta order is preserved.
    $this->assertSame(
      $this->referencedNodes[1]->getRevisionId(),
      $result[0]->getRevisionId(),
      'First item resolves correct revision.'
    );
    $this->assertSame(
      $this->referencedNodes[1]->label(),
      $result[0]->label(),
      'First item label matches expected.'
    );

    $this->assertSame(
      $this->referencedNodes[0]->getRevisionId(),
      $result[1]->getRevisionId(),
      'Second item resolves correct revision.'
    );
    $this->assertSame(
      $this->referencedNodes[0]->label(),
      $result[1]->label(),
      'Second item label matches expected.'
    );
  }

  /**
   * Tests that inaccessible entities are filtered when access=TRUE.
   *
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Field\EntityReferenceRevisions::resolve
   */
  public function testAccessFilteringRemovesInaccessible(): void {
    // Hide the first referenced node from view to make it inaccessible.
    $this->referencedNodes[0]->set('status', FALSE)->save();

    // Create a host node with pre-resolved entities (including the hidden one).
    $hostNode = Node::create(['title' => 'Host with hidden ref', 'type' => 'test1']);
    $hostNode->set('field_ref1', [
      [
        'target_id' => $this->referencedNodes[0]->id(),
        'target_revision_id' => $this->referencedNodes[0]->getRevisionId(),
      ],
      [
        'target_id' => $this->referencedNodes[1]->id(),
        'target_revision_id' => $this->referencedNodes[1]->getRevisionId(),
      ],
    ]);

    // Execute with access=TRUE (should filter out hidden node).
    $resultWithAccess = $this->executeDataProducer('entity_reference_revisions', [
      'entity' => $hostNode,
      'field' => 'field_ref1',
      'access' => TRUE,
      'access_operation' => 'view',
    ]);

    // Execute with access=FALSE (should return all nodes including hidden).
    $resultWithoutAccess = $this->executeDataProducer('entity_reference_revisions', [
      'entity' => $hostNode,
      'field' => 'field_ref1',
      'access' => FALSE,
      'access_operation' => 'view',
    ]);

    // With access=TRUE, only the published node should be returned.
    $this->assertCount(1, $resultWithAccess, 'Result with access=TRUE filters out inaccessible entity.');
    $this->assertSame(
      $this->referencedNodes[1]->id(),
      $resultWithAccess[0]->id(),
      'Only the accessible entity is returned with access=TRUE.'
    );

    // With access=FALSE, both nodes should be returned.
    $this->assertCount(2, $resultWithoutAccess, 'Result with access=FALSE returns all entities.');
  }

}
