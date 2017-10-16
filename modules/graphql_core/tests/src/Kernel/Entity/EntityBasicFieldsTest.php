<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\UserCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;
use DateTime;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class EntityBasicFieldsTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;
  use UserCreationTrait;

  public static $modules = [
    'graphql_core',
    'node',
    'field',
    'filter',
    'text',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');

    $this->createContentType([
      'type' => 'test',
    ]);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('edit any test content')
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
    $user = $this->createUser();

    $node = $this->createNode([
      'title' => 'Node in default language',
      'type' => 'test',
      'status' => 1,
      'uid' => $user->id(),
    ]);

    $translation = $node->addTranslation('fr', ['title' => 'French node']);
    $translation->save();

    $result = $this->executeQueryFile('basic_fields.gql', [
      'nid' => (int) $node->id(),
    ]);

    $created = (new DateTime())->setTimestamp($node->getCreatedTime())->format(DateTime::ISO8601);
    $changed = (new DateTime())->setTimestamp($node->getChangedTime())->format(DateTime::ISO8601);

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
      'entityOwner' => [
        'entityLabel' => $user->label(),
      ],
      'entityTranslation' => [
        'entityLabel' => $translation->label(),
      ],
      // EntityPublishedInterface has been added with 8.3.
      // Below the field will return false.
      'entityPublished' => version_compare(\Drupal::VERSION, '8.3', '<') ? FALSE : TRUE,
      'entityCreated' => $created,
      'entityChanged' => $changed,
      'viewAccess' => TRUE,
      'updateAccess' => TRUE,
      'deleteAccess' => FALSE,
    ];

    $this->assertEquals($values, $result['data']['node']['entities'][0], 'Content type Interface resolves basic entity fields.');
  }

}
