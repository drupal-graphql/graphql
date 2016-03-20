<?php

/**
 * @file
 * Contains \Drupal\graphql\Tests\QueryTestBase.
 */

namespace Drupal\graphql\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test helper class for GraphQL query tests.
 */
abstract class QueryTestBase extends WebTestBase {

  /**
   * The GraphQL resource.
   *
   * @var string
   */
  protected $queryUrl = 'graphql';

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('graphql', 'node');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user with the proper permissions and log in.
    $this->drupalLogin($this->drupalCreateUser(['execute graphql requests']));

    // Create a test content type for node testing.
    $this->drupalCreateContentType(['name' => 'graphqltest', 'type' => 'graphqltest']);
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
    // @todo Generate CSRF token.
    $token = '';

    $body = [
      'query' => $query,
      'variables' => $variables,
      'operation' => $operation,
    ];

    $options = [
      CURLOPT_HTTPGET => FALSE,
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => json_encode($body),
      CURLOPT_URL => $this->buildUrl($this->queryUrl),
      CURLOPT_NOBODY => FALSE,
    ];

    $body = $this->curlExec($options);
    $headers = $this->drupalGetHeaders();

    $this->verbose(
      '<hr />Code: ' . curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE) .
      '<hr />Response headers: ' . nl2br(print_r($headers, TRUE)) .
      '<hr />Response body: ' .  $body
    );

    return $body;
  }

  /**
   * Check to see if the HTTP request response body is identical to the expected
   * value.
   *
   * @param array $expected
   *   The expected value.
   * @param array $actual
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
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  protected function assertResponseBody(array $expected, array $actual, $message = '', $group = 'GraphQL Response') {
    $expected = json_decode($expected);
    $actual = json_decode($actual);

    return $this->assertIdentical($expected, $actual, $message ? $message : strtr('Response body @expected (expected) is equal to @response (actual).', array('@expected' => var_export($expected, TRUE), '@response' => var_export($actual, TRUE))), $group);
  }
}
