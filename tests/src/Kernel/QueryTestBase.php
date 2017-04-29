<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\Role;
use Symfony\Component\HttpFoundation\Request;

abstract class QueryTestBase extends KernelTestBase  {

  use NodeCreationTrait;

  /**
   * The GraphQL resource.
   *
   * @var string
   */
  protected $queryUrl = 'graphql';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', 'router');
    $this->installConfig('user');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    \Drupal::service('router.builder')->rebuild();

    Role::load('anonymous')
      ->grantPermission('execute graphql requests')
      ->save();

    // Create a test content type for node testing.
    NodeType::create([
      'name' => 'article',
      'type' => 'article',
    ])->save();
  }

  /**
   * Helper function to issue a HTTP request with simpletest's cURL.
   *
   * @param $query
   * @param array $variables
   * @param string|null $operation
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  protected function query($query, array $variables = NULL, $operation = NULL) {
    $body = [
      'query' => $query,
      'variables' => $variables,
      'operation' => $operation,
    ];

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = \Drupal::service('http_kernel');
    return $http_kernel->handle(Request::create('/graphql', 'GET', [], [], [], [], json_encode($body)));
  }

  /**
   * Check to see if the HTTP request response body is identical to the expected
   * value.
   *
   * @param array $expected
   *   The expected value as an array.
   * @param $actual
   *   The actual value.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Utility\SafeMarkup::format() to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   * @param string $group
   *   (optional) The group this message is in, which is displayed in a column
   *   in test output. Use 'Debug' to indicate this is debugging output. Do not
   *   translate this string. Defaults to 'Other'; most tests do not override
   *   this default.
   */
  protected function assertResponseBody(array $expected, $actual, $message = '', $group = 'GraphQL Response') {
    $expected = json_decode(json_encode($expected));
    $actual = json_decode($actual);

    $this->assertEquals($expected, $actual, $message ? $message : strtr('Response body @expected (expected) is equal to @response (actual).', [
      '@expected' => var_export($expected, TRUE),
      '@response' => var_export($actual, TRUE),
    ]));
  }

}
