<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test access restrictions on secure fields.
 *
 * @group graphql
 */
class SecureFieldTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function userPermissions() {
    // Don't grant the user field security bypass for this test case.
    return [
      'execute graphql requests',
      //'bypass graphql field security',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->mockField('secure', [
      'name' => 'secure',
      'type' => 'Boolean',
      'secure' => TRUE,
    ], new CacheableValue(TRUE, [(new CacheableMetadata())->addCacheContexts(['user.permissions'])]));

    $this->mockField('insecure', [
      'name' => 'insecure',
      'type' => 'Boolean',
      'secure' => FALSE,
    ], new CacheableValue(TRUE, [(new CacheableMetadata())->addCacheContexts(['user.permissions'])]));
  }

  /**
   * Test if a secure field is accessible.
   */
  public function testSecureField() {
    $this->assertResults('{ secure }', [], [
      'secure' => TRUE,
    ], $this->defaultCacheMetaData());
  }

  /**
   * Test if an insecure field is accessible.
   */
  public function testInsecureField() {
    $metadata = $this->defaultCacheMetaData();
    $metadata->setCacheMaxAge(0);
    $this->assertErrors('{ insecure }', [], [
      'Unable to resolve insecure field \'insecure\'.',
    ], $metadata);
  }

  /**
   * Check if the bypass permission works as expected.
   */
  public function testByPassFieldSecurity() {
    $this->accountProphecy
      ->hasPermission('bypass graphql field security')
      ->willReturn(TRUE);

    $this->assertResults('{ insecure }', [], [
      'insecure' => TRUE,
    ], $this->defaultCacheMetaData());
  }

}
