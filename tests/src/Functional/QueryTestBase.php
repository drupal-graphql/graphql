<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

abstract class QueryTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

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
  }

  /**
   * Helper function to issue a HTTP request with simpletest's cURL.
  &
   * @param $query
   * @param array $variables
   * @param string|null $operation
   * @param string[] $parameters
   *
   * @return string The content returned from the request.
   * The content returned from the request.
   */
  protected function query($query, array $variables = NULL, $operation = NULL, $parameters = []) {
    $body = [
      'query' => $query,
      'variables' => $variables,
      'operation' => $operation,
    ];

    // Ensure that requests are made in the right session.
    $minkSession = $this->getSession()->getCookie($this->getSessionName());

    $cookie = new SetCookie();
    $cookie->setName($this->getSessionName());
    $cookie->setValue($minkSession);
    $cookie->setDomain(parse_url($this->baseUrl, PHP_URL_HOST));

    $jar = new CookieJar();
    $jar->setCookie($cookie);

    $response = \Drupal::httpClient()->post($this->getAbsoluteUrl($this->queryUrl), [
      'body' => json_encode($body),
      'cookies' => $jar,
      'query' => $parameters,
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
