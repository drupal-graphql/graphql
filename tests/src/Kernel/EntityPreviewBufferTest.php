<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the entity preview buffer service.
 *
 * @group graphql
 */
class EntityPreviewBufferTest extends GraphQLTestBase {

  /**
   * List of nodes created for preview testing.
   *
   * @var array<int, \Drupal\node\Entity\Node>
   */
  protected array $nodes = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    NodeType::create([
      'type' => 'test',
      'name' => 'Test',
    ])->save();

    // Create three nodes and prepare preview entities for them via the node
    // form preview workflow (unsaved changes are stored for preview).
    foreach (range(1, 3) as $i) {
      $node = Node::create([
        'title' => 'Node ' . $i,
        'type' => 'test',
      ]);
      $node->save();

      // Prepare a preview for this node with an altered title to ensure the
      // preview entity is returned by the buffer.
      /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
      $entity_type_manager = \Drupal::service('entity_type.manager');
      /** @var \Drupal\node\Form\NodeForm $form_object */
      $form_object = $entity_type_manager->getFormObject('node', 'default');
      $form_object->setEntity($node);

      $form_state = new FormState();
      $form_state->setFormObject($form_object);

      $new_title = sprintf('Node %d (Preview Title)', $i);
      /** @var \Drupal\node\Entity\Node $node */
      $node = $form_object->getEntity();
      $node->setTitle($new_title);
      // This stores the preview entity in temp storage keyed by UUID so the
      // ParamConverter (and thus the buffer) can retrieve it.
      $form_object->preview([], $form_state);

      // Keep a reference and also ensure our modified title is what we expect
      // for later assertions.
      /* @phpstan-ignore-next-line */
      $node->_preview_expected_title = $new_title;
      $this->nodes[] = $node;
    }
  }

  /**
   * Ensures the preview buffer resolves single and multiple UUIDs correctly.
   */
  public function testEntityPreviewBuffer(): void {
    /** @var \Drupal\graphql\GraphQL\Buffers\EntityPreviewBuffer $buffer */
    $buffer = \Drupal::service('graphql.buffer.entity_preview');

    // Single resolvers for each preview UUID.
    $resolverA = $buffer->add('node', $this->nodes[0]->uuid());
    $resolverB = $buffer->add('node', $this->nodes[1]->uuid());
    $resolverC = $buffer->add('node', $this->nodes[2]->uuid());

    $entityA = $resolverA();
    $entityB = $resolverB();
    $entityC = $resolverC();

    $this->assertInstanceOf(Node::class, $entityA);
    $this->assertInstanceOf(Node::class, $entityB);
    $this->assertInstanceOf(Node::class, $entityC);

    $this->assertSame($this->nodes[0]->uuid(), $entityA->uuid());
    $this->assertSame($this->nodes[1]->uuid(), $entityB->uuid());
    $this->assertSame($this->nodes[2]->uuid(), $entityC->uuid());

    // Ensure the unsaved preview titles are reflected (i.e., preview entities
    // are returned rather than freshly loaded from storage).
    $this->assertSame($this->nodes[0]->_preview_expected_title, $entityA->label());
    $this->assertSame($this->nodes[1]->_preview_expected_title, $entityB->label());
    $this->assertSame($this->nodes[2]->_preview_expected_title, $entityC->label());

    // Also verify that passing an array of UUIDs returns an array of entities
    // in the same order, filtered to those that exist.
    $resolverArray = $buffer->add('node', [
      $this->nodes[2]->uuid(),
      'non-existent-uuid',
      $this->nodes[0]->uuid(),
    ]);
    $entities = $resolverArray();

    $this->assertIsArray($entities);
    $this->assertCount(2, $entities);
    $this->assertInstanceOf(Node::class, $entities[0]);
    $this->assertInstanceOf(Node::class, $entities[1]);
    $this->assertSame($this->nodes[2]->uuid(), $entities[0]->uuid());
    $this->assertSame($this->nodes[2]->_preview_expected_title, $entities[0]->label());
    $this->assertSame($this->nodes[0]->uuid(), $entities[1]->uuid());
    $this->assertSame($this->nodes[0]->_preview_expected_title, $entities[1]->label());
  }

}
