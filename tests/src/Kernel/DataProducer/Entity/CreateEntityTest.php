<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Test the CreateEntity producer.
 *
 * @group graphql
 */
class CreateEntityTest extends GraphQLTestBase {

  use DataProducerExecutionTrait;
  use UserCreationTrait;

  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $pluginId = 'create_entity';

  /**
   * Test creating entities.
   */
  public function testCreateEntity() {
    $content_type = NodeType::create([
      'type' => 'lorem',
      'name' => 'ipsum',
    ]);
    $content_type->save();

    $result = $this->executeDataProducer($this->pluginId, [
      'entity_type' => 'node',
      'values' => [],
      'entity_return_key' => 'foo',
    ]);
    $this->assertSame('Access was forbidden.', $result['errors'][0]);

    $this->setCurrentUser($this->createUser(['bypass node access', 'access content']));

    $result = $this->executeDataProducer($this->pluginId, [
      'entity_type' => 'node',
      'values' => [
        'type' => 'lorem'
      ],
      'entity_return_key' => 'foo',
    ]);
    $this->assertSame([
      'title: This value should not be null.',
    ], $result['errors']);

    $result = $this->executeDataProducer($this->pluginId, [
      'entity_type' => 'node',
      'save' => TRUE,
      'values' => [
        'type' => 'lorem',
        'title' => 'bar',
      ],
      'entity_return_key' => 'foo',
    ]);
    $this->assertEquals('bar', $result['foo']->label());
    $this->assertFalse($result['foo']->isNew());

    $result = $this->executeDataProducer($this->pluginId, [
      'entity_type' => 'node',
      'save' => FALSE,
      'values' => [
        'type' => 'lorem',
        'title' => 'bar',
      ],
      'entity_return_key' => 'foo',
    ]);
    $this->assertEquals('bar', $result['foo']->label());
    $this->assertTrue($result['foo']->isNew());
  }

}
