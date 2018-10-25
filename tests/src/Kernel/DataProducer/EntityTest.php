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
 * Data producers Entity test class.
 *
 * @group graphql
 */
class EntityTest extends GraphQLTestBase {

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

    $this->node = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
    ]);
    $this->node->save();

    $this->translation_fr = $this->node->addTranslation('fr', ['title' => 'sit amet fr']);
    $this->translation_fr->save();

    $this->translation_de = $this->node->addTranslation('de', ['title' => 'sit amet de']);
    $this->translation_de->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'lorem', TRUE);
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityBundle::resolve
   */
  public function testResolveBundle() {
    $this->entity->expects($this->once())
      ->method('bundle')
      ->willReturn('page');

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_bundle',
      'configuration' => []
    ]);
    $this->assertEquals('page', $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityChanged::resolve
   */
  public function testResolveChanged() {
    $this->entity->expects($this->once())
      ->method('getChangedTime')
      ->willReturn(17000000000);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_changed',
      'configuration' => []
    ]);
    $this->assertEquals('2508-09-16', $plugin->resolve($this->entity, 'Y-m-d'));
    $this->assertNull($plugin->resolve($this->entity_interface, 'Y-m-d'));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityCreated::resolve
   */
  public function testResolveCreated() {
    $this->entity->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn(17000000000);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_created',
      'configuration' => []
    ]);
    $this->assertEquals('2508-09-16', $plugin->resolve($this->entity, 'Y-m-d'));
    $this->assertNull($plugin->resolve($this->entity_interface, 'Y-m-d'));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityDescription::resolve
   */
  public function testResolveDescription() {
    $entity = $this->getMockBuilder(EntityTestBundle::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity->expects($this->once())
      ->method('getDescription')
      ->willReturn('Dummy description');

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_description',
      'configuration' => []
    ]);
    $this->assertEquals('Dummy description', $plugin->resolve($entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityId::resolve
   */
  public function testResolveId() {
    $this->entity->expects($this->once())
      ->method('id')
      ->willReturn(5);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_id',
      'configuration' => []
    ]);
    $this->assertEquals(5, $plugin->resolve($this->entity));
  }


  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLabel::resolve
   */
  public function testResolveLabel() {
    $this->entity->expects($this->once())
      ->method('label')
      ->willReturn('Dummy label');

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_label',
      'configuration' => []
    ]);
    $this->assertEquals('Dummy label', $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLanguage::resolve
   */
  public function testResolveLanguage() {
    $language = $this->getMockBuilder(LanguageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($language);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_language',
      'configuration' => []
    ]);
    $this->assertEquals($language, $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityOwner::resolve
   */
  public function testResolveOwner() {
    $this->entity->expects($this->once())
      ->method('getOwner')
      ->willReturn($this->user);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_owner',
      'configuration' => []
    ]);
    $this->assertEquals($this->user, $plugin->resolve($this->entity));
    $this->assertNull($plugin->resolve($this->entity_interface));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityType::resolve
   */
  public function testResolveEntityTypeId() {
    $this->entity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('test_graphql');

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_type_id',
      'configuration' => []
    ]);
    $this->assertEquals('test_graphql', $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityPublished::resolve
   */
  public function testResolvePublished() {
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_published',
      'configuration' => []
    ]);
    $this->assertEquals(TRUE, $plugin->resolve($this->entity));
    $this->assertNull($plugin->resolve($this->entity_interface));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityAccess::resolve
   */
  public function testResolveAccess() {
    $this->entity->expects($this->any())
      ->method('access')
      ->willReturn(FALSE);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_access',
      'configuration' => []
    ]);
    $this->assertFalse($plugin->resolve($this->entity, 'delete', $this->user));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslation::resolve
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslations::resolve
   */
  public function testResolveTranslation() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_translation',
      'configuration' => []
    ]);

    $translated = $plugin->resolve($this->node, 'fr');
    $this->assertEquals('sit amet fr', $translated->label());

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_translations',
      'configuration' => []
    ]);

    $translations = $plugin->resolve($this->node);
    $this->assertEquals('Dolor', $translations['en']->label());
    $this->assertEquals('sit amet fr', $translations['fr']->label());
    $this->assertEquals('sit amet de', $translations['de']->label());
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUrl::resolve
   */
  public function testResolveUrl() {
    $url = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->entity->expects($this->once())
      ->method('toUrl')
      ->willReturn($url);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_url',
      'configuration' => []
    ]);
    $this->assertEquals($url, $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUuid::resolve
   */
  public function testResolveUuid() {
    $this->entity->expects($this->once())
      ->method('uuid')
      ->willReturn('some uuid');

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_uuid',
      'configuration' => []
    ]);
    $this->assertEquals('some uuid', $plugin->resolve($this->entity));
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityLoad() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load',
      'configuration' => []
    ]);

    $deferred = $plugin->resolve($this->node->getEntityTypeId(), $this->node->id(), NULL, NULL, $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);
    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoadByUuid::resolve
   */
  public function testResolveEntityLoadByUuid() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load_by_uuid',
      'configuration' => []
    ]);

    $deferred = $plugin->resolve($this->node->getEntityTypeId(), $this->node->uuid(), NULL, NULL, $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);
    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveUnknownEntityLoad() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load',
      'configuration' => []
    ]);

    $deferred = $plugin->resolve($this->node->getEntityTypeId(), 0, NULL, NULL, $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);
    $this->assertContains('node_list', $metadata->getCacheTags());
    $this->assertNull($result);
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveMismatchEntityLoad() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load',
      'configuration' => []
    ]);

    $deferred = $plugin->resolve('node', $this->node->id(), NULL, ['otherbundle'], $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);
    $this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertNull($result);
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveTranslatedEntityLoad() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_load',
      'configuration' => []
    ]);

    $deferred = $plugin->resolve('node', $this->node->id(), 'fr', NULL, $metadata);

    $adapter = new SyncPromiseAdapter();
    $promise = $adapter->convertThenable($deferred);

    $result = $adapter->wait($promise);
    $this->assertEquals('fr', $result->language()->getId());
    $this->assertEquals('sit amet fr', $result->getTitle());
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityRendered() {
    $metadata = $this->defaultCacheMetaData();

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_rendered',
      'configuration' => []
    ]);

    $result = $plugin->resolve($this->node, 'default', $metadata);
    $this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertContains('<a href="/node/1" rel="bookmark"><span>' . $this->node->getTitle() . '</span>', $result);
  }

}
