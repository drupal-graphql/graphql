<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\user\UserInterface;

/**
 * Data producers Entity test class.
 *
 * @group graphql
 */
class EntityTest extends GraphQLTestBase {

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Mocked test entity.
   *
   * @var \Drupal\node\NodeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entity;

  /**
   * Mocked test entity interface.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entityInterface;

  /**
   * Mocked test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Translated test entity.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $translationFr;

  /**
   * Translated test entity.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $translationDe;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->entity = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityInterface = $this->getMockBuilder(EntityInterface::class)
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

    $this->translationFr = $this->node->addTranslation('fr', ['title' => 'sit amet fr']);
    $this->translationFr->save();

    $this->translationDe = $this->node->addTranslation('de', ['title' => 'sit amet de']);
    $this->translationDe->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'lorem', TRUE);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityBundle::resolve
   */
  public function testResolveBundle(): void {
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
  public function testResolveChanged(): void {
    $this->entity->expects($this->once())
      ->method('getChangedTime')
      ->willReturn(17000000000);

    $this->assertEquals('2508-09-16', $this->executeDataProducer('entity_changed', [
      'format' => 'Y-m-d',
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_changed', [
      'format' => 'Y-m-d',
      'entity' => $this->entityInterface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityCreated::resolve
   */
  public function testResolveCreated(): void {
    $this->entity->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn(17000000000);

    $this->assertEquals('2508-09-16', $this->executeDataProducer('entity_created', [
      'format' => 'Y-m-d',
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_created', [
      'format' => 'Y-m-d',
      'entity' => $this->entityInterface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityDescription::resolve
   */
  public function testResolveDescription(): void {
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
  public function testResolveId(): void {
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
  public function testResolveLabel(): void {
    $this->entity->expects($this->once())
      ->method('label')
      ->willReturn('Dummy label');

    $this->entity->expects($this->exactly(2))
      ->method('access')
      ->willReturnCallback(static function (): AccessResult {
        static $counter = 0;
        switch ($counter) {
          case 0:
            $counter++;
            return AccessResult::allowed();

          case 1:
            $counter++;
            return AccessResult::forbidden();

          default:
            throw new \LogicException('The access() method should not have been called more than twice.');
        }
      })
      ->with('view label', NULL, TRUE);

    $this->assertEquals('Dummy label', $this->executeDataProducer('entity_label', [
      'entity' => $this->entity,
      // @todo Remove when https://github.com/drupal-graphql/graphql/issues/1233 is fixed.
      'access' => TRUE,
    ]));

    $this->assertNull($this->executeDataProducer('entity_label', [
      'entity' => $this->entity,
      // @todo Remove when https://github.com/drupal-graphql/graphql/issues/1233 is fixed.
      'access' => TRUE,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLanguage::resolve
   */
  public function testResolveLanguage(): void {
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
  public function testResolveOwner(): void {
    $this->entity->expects($this->once())
      ->method('getOwner')
      ->willReturn($this->user);

    $this->assertEquals($this->user, $this->executeDataProducer('entity_owner', [
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_owner', [
      'entity' => $this->entityInterface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityType::resolve
   */
  public function testResolveEntityTypeId(): void {
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
  public function testResolvePublished(): void {
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);

    $this->assertEquals(TRUE, $this->executeDataProducer('entity_published', [
      'entity' => $this->entity,
    ]));

    $this->assertNull($this->executeDataProducer('entity_published', [
      'entity' => $this->entityInterface,
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityAccess::resolve
   */
  public function testResolveAccess(): void {
    $this->entity->expects($this->any())
      ->method('access')
      ->willReturn(new AccessResultForbidden());

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
  public function testResolveTranslation(): void {
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
  public function testResolveUrl(): void {
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
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUrl::resolve
   */
  public function testResolveAbsoluteUrl(): void {
    $url = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entity->expects($this->once())
      ->method('toUrl')
      ->with('canonical', ['absolute' => TRUE])
      ->willReturn($url);

    $this->assertEquals($url, $this->executeDataProducer('entity_url', [
      'entity' => $this->entity,
      'rel' => 'canonical',
      'options' => ['absolute' => TRUE],
    ]));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityUuid::resolve
   */
  public function testResolveUuid(): void {
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

    // @todo Add metadata check.
    // $this->assertContains('node_list', $metadata->getCacheTags());
    $this->assertNull($result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad::resolve
   */
  public function testResolveMismatchEntityLoad(): void {
    $result = $this->executeDataProducer('entity_load', [
      'type' => $this->node->getEntityTypeId(),
      'id' => $this->node->id(),
      'bundles' => ['otherbundle'],
    ]);

    // @todo Add metadata check.
    // $this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertNull($result);
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

    $this->assertEquals('fr', $result->language()->getId());
    $this->assertEquals('sit amet fr', $result->getTitle());
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
    $result = $this->executeDataProducer('entity_rendered', [
      'entity' => $this->node,
      'mode' => 'default',
    ]);

    // @todo Add metadata check.
    // $this->assertContains('node:1', $metadata->getCacheTags());
    $this->assertMatchesRegularExpression('#<a href="/node/1" rel="bookmark">\s*<span>' . $this->node->getTitle() . '</span>#', $result);
  }

}
