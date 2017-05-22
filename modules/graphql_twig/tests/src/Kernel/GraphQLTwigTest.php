<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Test GraphQL Twig integration.
 */
class GraphQLTwigTest extends KernelTestBase {
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'datetime',
    'user',
    'system',
    'filter',
    'field',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', 'sequences');
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig('filter');
    $this->installConfig('node');

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test a page callback rendered by twig.
   */
  public function testPageCallback() {
    $node = $this->createNode([
      'title' => 'test',
      'status' => 1,
    ]);
  }

}
