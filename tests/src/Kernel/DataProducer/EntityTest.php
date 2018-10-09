<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;

/**
 * Data producers test base class.
 *
 * @group graphql
 */
class EntityTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'graphql',
  ];

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
    $entity = $this->getMockBuilder([EntityInterface::class, EntityDescriptionInterface::class])
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
    $user = $this->getMockBuilder(UserInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->entity->expects($this->once())
      ->method('getOwner')
      ->willReturn($user);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_owner',
      'configuration' => []
    ]);
    $this->assertEquals($user, $plugin->resolve($this->entity));
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

}
