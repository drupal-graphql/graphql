<?php

namespace Drupal\Tests\graphql\Functional;

/**
 * Tests currentUser field.
 *
 * @group GraphQL
 */
class CurrentUserTest extends QueryTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_test_custom_schema'];

  /**
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->drupalCreateUser(['execute graphql requests'], 'kitten');
    $this->drupalLogin($this->user);
  }

  /**
   * @covers \Drupal\Tests\graphql\Functional\QueryTestBase::query
   */
  public function testCurrentUser() {
    $query = <<<GQL
{
  currentUser() {
    username
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'currentUser' => [
          'username' => $this->user->getDisplayName(),
        ],
      ],
    ], $data);
  }

}
