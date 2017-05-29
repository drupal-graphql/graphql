<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class EntityBasicFieldsTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'language',
    'content_translation',
    'graphql_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');

    $this->createContentType([
      'type' => 'test',
    ]);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();

    $language = $this->container->get('entity.manager')->getStorage('configurable_language')->create([
      'id' => 'fr',
    ]);
    $language->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testBasicFields() {
    $node = $this->createNode([
      'title' => 'Node in default language',
      'type' => 'test',
      'status' => 1,
    ]);

    $translation = $node->addTranslation('fr', ['title' => 'French node']);
    $translation->save();

    $result = $this->executeQueryFile('basic_fields.gql', [
      'path' => '/node/' . $node->id(),
    ]);

    $values = [
      'entityId' => $node->id(),
      'entityUuid' => $node->uuid(),
      'entityLabel' => $node->label(),
      'entityType' => $node->getEntityTypeId(),
      'entityBundle' => $node->bundle(),
      'entityLanguage' => [
        'id' => $node->language()->getId(),
        'name' => $node->language()->getName(),
        'direction' => $node->language()->getDirection(),
        'weight' => $node->language()->getWeight(),
      ],
      'entityRoute' => [
        'internalPath' => '/node/' . $node->id(),
        'aliasedPath' => '/node/' . $node->id(),
      ],
      'entityTranslation' => [
        'entityLabel' => $translation->label(),
      ]
    ];

    $this->assertEquals($values, $result['data']['route']['node'], 'Content type Interface resolves basic entity fields.');
    $this->assertEquals($values, $result['data']['route']['node_test'], 'Content bundle Type resolves basic entity fields.');
  }

}
