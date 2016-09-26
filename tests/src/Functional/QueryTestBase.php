<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\simpletest\BrowserTestBase;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\Role;

abstract class QueryTestBase extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql', 'node'];

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

    // Create a user with the proper permissions and log in.
    $this->drupalLogin($this->drupalCreateUser(['execute graphql requests']));

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
  &
   * @param $query
   * @param array $variables
   * @param string|null $operation
   *
   * @return string The content returned from the request.
   * The content returned from the request.
   */
  protected function query($query, array $variables = NULL, $operation = NULL) {
    $body = [
      'query' => $query,
      'variables' => $variables,
      'operation' => $operation,
    ];

    $response = \Drupal::httpClient()->post($this->getAbsoluteUrl($this->queryUrl), [
      'body' => json_encode($body),
    ]);

    return (string) $response->getBody();
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
