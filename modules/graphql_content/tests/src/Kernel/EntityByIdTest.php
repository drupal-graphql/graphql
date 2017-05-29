<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\NodeInterface;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_content
 */
class EntityByIdTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'language',
    'content_translation',
    'text',
    'graphql_content',
  ];

  /**
   * The added language.
   *
   * @var string
   */
  protected $langcode = 'fr';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    $language = $this->container->get('entity.manager')->getStorage('configurable_language')->create([
      'id' => $this->langcode,
    ]);
    $language->save();
  }

  /**
   * Test that the entity query returns all nodes if no args are given.
   */
  public function testEntityByIdWithTranslation() {
    $node = $this->createNode([
      'title' => 'English node',
      'type' => 'test',
    ]);
    $node->save();
    $node->addTranslation($this->langcode, ['title' => 'French node'])->save();

    // Check English node.
    $result = $this->executeQueryFile('entity_by_id.gql', [
      'id' => $node->id(),
      'language' => 'en',
    ]);
    $this->assertEquals(['entityLabel' => 'English node'], $result['data']['nodeById']);

    // Check French translation.
    $result = $this->executeQueryFile('entity_by_id.gql', [
      'id' => $node->id(),
      'language' => 'fr',
    ]);
    $this->assertEquals(['entityLabel' => 'French node'], $result['data']['nodeById']);
  }

}
