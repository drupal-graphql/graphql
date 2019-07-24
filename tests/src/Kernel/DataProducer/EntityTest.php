<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Drupal\entity_test\Entity\EntityTestBundle;

/**
 * Data producers Entity test class.
 *
 * @group graphql
 */
class EntityTest extends GraphQLTestBase {

  /**
   * @var NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

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
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityBundle::resolve
   */
  public function testResolveBundle() {
    $this->entity->expects($this->once())
      ->method('bundle')
      ->willReturn('page');

    $result = $this->executeDataProducer('entity_bundle', [
      'entity' => $this->entity,
    ]);

    $this->assertEquals('page', $result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityChanged::resolve
   */
  public function testResolveChanged() {
    $this->entity->expects($this->once())
      ->method('getChangedTime')
      ->willReturn(17000000000);

    $this->assertEquals('2508-09-16', $this->executeDataProducer('entity_changed', [
      'format' => 'Y-m-d',
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_changed', [
      'format' => 'Y-m-d',
      'entity' => $this->entity_interface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityCreated::resolve
   */
  public function testResolveCreated() {
    $this->entity->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn(17000000000);

    $this->assertEquals('2508-09-16', $this->executeDataProducer('entity_created', [
      'format' => 'Y-m-d',
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_created', [
      'format' => 'Y-m-d',
      'entity' => $this->entity_interface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityDescription::resolve
   */
  public function testResolveDescription() {
    $entity = $this->getMockBuilder(EntityTestBundle::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity->expects($this->once())
      ->method('getDescription')
      ->willReturn('Dummy description');

    $this->assertEquals('Dummy description', $this->executeDataProducer('entity_description', [
      'entity' => $entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityId::resolve
   */
  public function testResolveId() {
    $this->entity->expects($this->once())
      ->method('id')
      ->willReturn(5);

    $this->assertEquals(5, $this->executeDataProducer('entity_id', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLabel::resolve
   */
  public function testResolveLabel() {
    $this->entity->expects($this->once())
      ->method('label')
      ->willReturn('Dummy label');

    $this->assertEquals('Dummy label', $this->executeDataProducer('entity_label', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLanguage::resolve
   */
  public function testResolveLanguage() {
    $language = $this->getMockBuilder(LanguageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($language);

    $this->assertEquals($language, $this->executeDataProducer('entity_language', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityOwner::resolve
   */
  public function testResolveOwner() {
    $this->entity->expects($this->once())
      ->method('getOwner')
      ->willReturn($this->user);

    $this->assertEquals($this->user, $this->executeDataProducer('entity_owner', [
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_owner', [
      'entity' => $this->entity_interface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityType::resolve
   */
  public function testResolveEntityTypeId() {
    $this->entity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('test_graphql');

    $this->assertEquals('test_graphql', $this->executeDataProducer('entity_type_id', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityPublished::resolve
   */
  public function testResolvePublished() {
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);

    $this->assertEquals(TRUE, $this->executeDataProducer('entity_published', [
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_published', [
      'entity' => $this->entity_interface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityAccess::resolve
   */
  public function testResolveAccess() {
    $this->entity->expects($this->any())
      ->method('access')
      ->willReturn(FALSE);

    $this->assertFalse($this->executeDataProducer('entity_access', [
      'entity' => $this->entity,
      'user' => $this->user,
      'operation' => 'delete',
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslation::resolve
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityTranslations::resolve
   */
  public function testResolveTranslation() {
    $french = $this->executeDataProducer('entity_translation', [
      'entity' => $this->node,
      'language' => 'fr',
    ]);

    $this->assertEquals('sit amet fr', $french->label());

    $translations = $this->executeDataProducer('entity_translations', [
      'entity' => $this->node,
    ]);

    $this->assertEquals('Dolor', $translations['en']->label());
    $this->assertEquals('sit amet fr', $translations['fr']->label());
    $this->assertEquals('sit amet de', $translations['de']->label());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUrl::resolve
   */
  public function testResolveUrl() {
    $url = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entity->expects($this->once())
      ->method('toUrl')
      ->willReturn($url);

    $this->assertEquals($url, $this->executeDataProducer('entity_url', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUuid::resolve
   */
  public function testResolveUuid() {
    $this->entity->expects($this->once())
      ->method('uuid')
      ->willReturn('some uuid');

    $this->assertEquals('some uuid', $this->executeDataProducer('entity_uuid', [
      'entity' => $this->entity,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityLoad() {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
    ]);

    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoadByUuid::resolve
   */
  public function testResolveEntityLoadByUuid() {
    $result = $this->executeDataProducer('entity_load_by_uuid', [
      'type' => $this->node->getEntityTypeId(),
      'uuid' => $this->node->uuid(),
    ]);

    $this->assertEquals($this->node->id(), $result->id());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveUnknownEntityLoad() {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => 0,
    ]);

    // TODO: Add metadata check.
    //$this->assertContains('node_list', $metadata->getCacheTags());
    $this->assertNull($result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveMismatchEntityLoad() {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
      'bundles' => ['otherbundle'],
    ]);

    // TODO: Add metadata check.
    //$this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertNull($result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveTranslatedEntityLoad() {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
      'language' => 'fr',
    ]);

    $this->assertEquals('fr', $result->language()->getId());
    $this->assertEquals('sit amet fr', $result->getTitle());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveEntityRendered() {
    $result = $this->executeDataProducer('entity_rendered', [
      'entity' => $this->node,
      'mode' => 'default',
    ]);

    // TODO: Add metadata check.
    //$this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertContains('<a href="/node/1" rel="bookmark"><span>' . $this->node->getTitle() . '</span>', $result);
  }

}
