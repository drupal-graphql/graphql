<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;
use Drupal\entity_test\Entity\EntityTestBundle;

/**
 * Data producers Entity multiple test class.
 *
 * @group graphql
 */
class EntityMultipleTest extends GraphQLTestBase {

  use QueryResultAssertionTrait;

  /**
   * @var NodeInterface
   */
  protected $node;

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

    $content_type = NodeType::create([
      'type' => 'otherbundle',
      'name' => 'otherbundle',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    $this->node1 = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
    ]);
    $this->node1->save();

    $this->node2 = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
    ]);
    $this->node2->save();
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

    $deferred = $plugin->resolve($this->node->getEntityTypeId(), [
      $this->node1->id(),
      $this->node2->id(),
    ], NULL, NULL, $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);

    $nids = [];
    foreach ($result as $item) {
      $nids[] = $item->id();
    }

    // All entity is loaded through entity load should match the initial values.
    $this->assertEquals($this->node->id(), $nids);
  }

}
