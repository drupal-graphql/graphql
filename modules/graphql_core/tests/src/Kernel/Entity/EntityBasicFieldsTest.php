<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;
use DateTime;

/**
 * Test basic entity fields.
 *
 * @group graphql_core
 */
class EntityBasicFieldsTest extends GraphQLContentTestBase {

  public static $modules = [
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $language = $this->container->get('entity.manager')->getStorage('configurable_language')->create([
      'id' => 'fr',
    ]);
    $language->save();
  }

  /**
   * Set the prophesized permissions.
   *
   * @return string[]
   *   The permissions to set on the prophesized user.
   */
  protected function userPermissions() {
    $perms = parent::userPermissions();
    $perms[] = 'edit any test content';
    return $perms;
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


    $query = $this->getQueryFromFile('basic_fields.gql');

    // TODO: Check cache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheContexts([
      'languages:language_content',
      'user.node_grants:view',
    ]);

    $metadata->addCacheTags([
      'entity_bundles',
      'entity_field_info',
      'entity_types',
      'node:1',
      'node_list',
      'user:1',
    ]);

    $this->assertResults($query, ['nid' => (string) $node->id()], [
      'node' => ['entities' => [$values]],
    ], $metadata);
  }

}
