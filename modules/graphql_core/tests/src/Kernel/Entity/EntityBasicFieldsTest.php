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
      'entityUrl' => [
        'path' => '/node/' . $node->id(),
      ],
      'entityOwner' => [
        'entityLabel' => $user->label(),
      ],
      // TODO: Fix this.
      'entityTranslation' => [
        'entityLabel' => $translation->label(),
      ],
      'entityPublished' => TRUE,
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
      'user.node_grants:view',
    ]);

    $metadata->addCacheTags([
      'node:1',
      'node_list',
      'user:2',
    ]);

    $this->assertResults($query, ['nid' => (string) $node->id()], [
      'node' => ['entities' => [$values]],
    ], $metadata);
  }

}
