<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Routing;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the RouteEntity data producer.
 *
 * @group graphql
 */
class RouteEntityTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $content_type = NodeType::create([
      'type' => 'event',
      'name' => 'Event',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    $this->published_node = Node::create([
      'title' => 'Test Event',
      'type' => 'event',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->published_node->save();

    $this->translation_fr_published = $this->published_node->addTranslation('fr', ['title' => 'Test Event FR']);
    $this->translation_fr_published->save();

    $this->translation_de_published = $this->published_node->addTranslation('de', ['title' => 'Test Event DE']);
    $this->translation_de_published->save();

    $this->unpublished_node = Node::create([
      'title' => 'Test Unpublished Event',
      'type' => 'event',
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $this->unpublished_node->save();

    $this->translation_fr_unpublished = $this->unpublished_node->addTranslation('fr', ['title' => 'Test Unpublished Event FR']);
    $this->translation_fr_unpublished->save();

    $this->translation_de_unpublished = $this->unpublished_node->addTranslation('de', ['title' => 'Test Unpublished Event DE']);
    $this->translation_de_unpublished->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'event', TRUE);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteEntity::resolve
   */
  public function testRouteEntity() {
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->published_node->id()]);

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
    ]);

    $this->assertEquals($this->published_node->id(), $result->id());
    $this->assertEquals($this->published_node->label(), $result->label());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'fr',
    ]);

    $this->assertEquals($this->translation_fr_published->id(), $result->id());
    $this->assertEquals($this->translation_fr_published->label(), $result->label());

    $result = $this->executeDataProducer('route_entity', [
      'url' => $url,
      'language' => 'de',
    ]);

    $this->assertEquals($this->translation_de_published->id(), $result->id());
    $this->assertEquals($this->translation_de_published->label(), $result->label());

    // Make sure we are not allowed to get the unpublished nodes or
    // translations.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $this->unpublished_node->id()]);
    foreach ([NULL, 'fr', 'de'] as $lang) {
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

    // TODO: Add cache checks.
//    $this->assertContains('node_list', $metadata->getCacheTags());
//    $this->assertContains('4xx-response', $metadata->getCacheTags());
  }

}
