<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;

/**
 * Data producers Entity multiple test class.
 *
 * @group graphql
 */
class EntityMultipleTest extends GraphQLTestBase {

  use QueryResultAssertionTrait;

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node1;

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node2;

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node3;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
    $this->entity = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->entity_interface = $this->getMockBuilder(EntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->user = $this->getMockBuilder(UserInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $content_type = NodeType::create([
      'type' => 'lorem',
      'name' => 'ipsum',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    $this->node1 = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->node1->save();

    $this->node2 = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->node2->save();

    $this->node3 = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $this->node3->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function userPermissions() {
    $permissions = parent::userPermissions();
    $permissions[] = 'access content';
    return $permissions;
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoadMultiple::resolve
   */
  public function testResolveEntityLoadMultiple() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load_multiple',
      'configuration' => [],
    ]);

    $deferred = $plugin->resolve($this->node1->getEntityTypeId(), [
      $this->node1->id(),
      $this->node2->id(),
      $this->node3->id(),
    ], NULL, [$this->node1->bundle(), $this->node2->bundle()], $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);

    $nids = [];
    foreach ($result as $item) {
      $nids[] = $item->id();
    }

    // All entity is loaded through entity load should match the initial values.
    // Hidden entity (node 3) is not include
    // because access checking will not return it.
    $this->assertEquals([
      $this->node1->id(),
      $this->node2->id(),
    ], $nids);
  }

}
