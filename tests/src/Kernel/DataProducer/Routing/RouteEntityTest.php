<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\Routing;

use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

/**
 * Test class for the RouteEntity data producer.
 *
 * @group graphql
 */
class RouteEntityTest extends GraphQLTestBase {

  /**
   * Published test node.
   */
  protected Node $publishedNode;

  /**
   * French translation of test node.
   */
  protected Node $translationFrPublished;

  /**
   * German translation of test node.
   */
  protected Node $translationDePublished;

  /**
   * Unpublished test node.
   */
  protected Node $unpublishedNode;

  /**
   * French translation of test node.
   */
  protected Node $translationFrUnpublished;

  /**
   * German translation of test node.
   */
  protected Node $translationDeUnpublished;

  /**
   * Unpublished test node.
   */
  protected Node $unpublishedToPublishedNode;

  /**
   * Published french translation of test node.
   */
  protected Node $translationFrUnpublishedToPublished;

  /**
   * Published German translation of test node.
   */
  protected Node $translationDeUnpublishedToPublished;

  /**
   * Published test node.
   */
  protected Node $publishedToUnpublishedNode;

  /**
   * Unpublished french translation of test node.
   */
  protected Node $translationFrPublishedToUnpublished;

  /**
   * Unpublished German translation of test node.
   */
  protected Node $translationDePublishedToUnpublished;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $content_type = NodeType::create([
      'type' => 'event',
      'name' => 'Event',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    // Published node and published translations.
    $this->publishedNode = Node::create([
      'title' => 'Test Event',
      'type' => 'event',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->publishedNode->save();

    $this->translationFrPublished = $this->publishedNode->addTranslation('fr', ['title' => 'Test Event FR']);
    $this->translationFrPublished->save();

    $this->translationDePublished = $this->publishedNode->addTranslation('de', ['title' => 'Test Event DE']);
    $this->translationDePublished->save();

    // Unpublished node and unpublished translations.
    $this->unpublishedNode = Node::create([
      'title' => 'Test Unpublished Event',
      'type' => 'event',
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $this->unpublishedNode->save();

    $this->translationFrUnpublished = $this->unpublishedNode->addTranslation('fr', ['title' => 'Test Unpublished Event FR']);
    $this->translationFrUnpublished->setUnpublished();
    $this->translationFrUnpublished->save();

    $this->translationDeUnpublished = $this->unpublishedNode->addTranslation('de', ['title' => 'Test Unpublished Event DE']);
    $this->translationDeUnpublished->setUnpublished();
    $this->translationDeUnpublished->save();

    // Unpublished node to published translations.
    $this->unpublishedToPublishedNode = Node::create([
      'title' => 'Test Unpublished to Published Event',
      'type' => 'event',
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $this->unpublishedToPublishedNode->save();

    $this->translationFrUnpublishedToPublished = $this->unpublishedToPublishedNode->addTranslation('fr', ['title' => 'Test Unpublished to Published Event FR']);
    $this->translationFrUnpublishedToPublished->setPublished();
    $this->translationFrUnpublishedToPublished->save();

    $this->translationDeUnpublishedToPublished = $this->unpublishedToPublishedNode->addTranslation('de', ['title' => 'Test Unpublished to Published Event DE']);
    $this->translationDeUnpublishedToPublished->setPublished();
    $this->translationDeUnpublishedToPublished->save();

    // Published node to unpublished translations.
    $this->publishedToUnpublishedNode = Node::create([
      'title' => 'Test Published to Unpublished Event',
      'type' => 'event',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->publishedToUnpublishedNode->save();

    $this->translationFrPublishedToUnpublished = $this->publishedToUnpublishedNode->addTranslation('fr', ['title' => 'Test Published to Unpublished Event FR']);
    $this->translationFrPublishedToUnpublished->setUnpublished();
    $this->translationFrPublishedToUnpublished->save();

    $this->translationDePublishedToUnpublished = $this->publishedToUnpublishedNode->addTranslation('de', ['title' => 'Test Published to Unpublished Event DE']);
    $this->translationDePublishedToUnpublished->setUnpublished();
    $this->translationDePublishedToUnpublished->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'event', TRUE);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteEntity::resolve
   */
  public function testRouteEntity(): void {
    // Published node to published translations.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->publishedNode->id()]);

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);

    $this->assertEquals($this->publishedNode->id(), $result->id());
    $this->assertEquals($this->publishedNode->label(), $result->label());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'fr',
    ]);

    $this->assertEquals($this->translationFrPublished->id(), $result->id());
    $this->assertEquals($this->translationFrPublished->label(), $result->label());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'de',
    ]);

    $this->assertEquals($this->translationDePublished->id(), $result->id());
    $this->assertEquals($this->translationDePublished->label(), $result->label());

    // Unpublished node to unpublished translations. Make sure we are not
    // allowed to get the unpublished nodes or translations.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->unpublishedNode->id()]);
    foreach ([NULL, 'fr', 'de'] as $lang) {
      $result = $this->executeDataProducer('route_entity', [
        'url' => $url,
        'language' => $lang,
      ]);

      $this->assertNull($result);
    }

    // Unpublished node to published translations. Make sure we are not able to
    // get unpublished source, but we are able to get published translations.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->unpublishedToPublishedNode->id()]);

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);

    $this->assertNull($result);

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'fr',
    ]);

    $this->assertEquals($this->translationFrUnpublishedToPublished->id(), $result->id());
    $this->assertEquals($this->translationFrUnpublishedToPublished->label(), $result->label());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'de',
    ]);

    $this->assertEquals($this->translationDeUnpublishedToPublished->id(), $result->id());
    $this->assertEquals($this->translationDeUnpublishedToPublished->label(), $result->label());

    // Published node to unpublished translations. Make sure we are able to get
    // published source, but we are not able to get unpublished translations.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->publishedToUnpublishedNode->id()]);

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);

    $this->assertEquals($this->publishedToUnpublishedNode->id(), $result->id());
    $this->assertEquals($this->publishedToUnpublishedNode->label(), $result->label());

    foreach (['fr', 'de'] as $lang) {
      $result = $this->executeDataProducer('route_entity', [
        'url' => $url,
        'language' => $lang,
      ]);

      $this->assertNull($result);
    }

    // Test with something which is not a URL.
    $this->assertNull($this->executeDataProducer('route_entity', [
      'url' => 'not_a_url',
    ]));

    // Test the 4xx response.
    $temp_node = Node::create([
      'title' => 'Temp node',
      'type' => 'event',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $temp_node->save();
    $url = Url::fromRoute('entity.node.canonical', ['node' => $temp_node->id()]);
    $temp_node->delete();

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);

    // The result has to be null and the metadata has to contain the node_list
    // and the 4xx-response cache tags.
    $this->assertNull($result);
    $this->assertContains('node_list', $this->fieldContext->getCacheTags());
    $this->assertContains('4xx-response', $this->fieldContext->getCacheTags());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteEntity::resolve
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteEntity::resolvePreview
   */
  public function testRouteEntityPreview(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = \Drupal::service('entity_type.manager');
    /** @var \Drupal\node\Form\NodeForm $form_object */
    $form_object = $entity_type_manager->getFormObject('node', 'default');
    $form_object->setEntity($this->publishedNode);

    $form_state = new FormState();
    $form_state->setFormObject($form_object);

    // Change the title in the form's entity without saving.
    $newTitle = 'Test Event (Preview Title)';
    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_object->getEntity();
    $node->setTitle($newTitle);
    $form_object->preview([], $form_state);

    // 1) Preview returns the entity, reflects the changed title, and disables
    // caching.
    $url = Url::fromRoute('entity.node.preview', [
      'node_preview' => $this->publishedNode->uuid(),
      'view_mode_id' => 'full',
    ]);
    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);
    $this->assertInstanceOf(Node::class, $result);
    $this->assertEquals($this->publishedNode->uuid(), $result->uuid());
    // Ensure the unsaved change from the form is reflected in preview.
    $this->assertSame($newTitle, $result->label());
    $this->assertSame(0, $this->fieldContext->getCacheMaxAge());

    // 2) Preview with language parameter returns the correct translation when
    // available.
    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'fr',
    ]);
    $this->assertInstanceOf(Node::class, $result);
    $this->assertSame($this->translationFrPublished->getTitle(), $result->label());
    $this->assertEquals($this->translationFrPublished->uuid(), $result->uuid());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'en',
    ]);
    $this->assertInstanceOf(Node::class, $result);
    $this->assertSame($newTitle, $result->label());
    $this->assertEquals($this->publishedNode->uuid(), $result->uuid());
  }

}
