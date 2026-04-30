<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the password_reset data producer.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\User\PasswordReset
 * @group graphql
 */
class PasswordResetTest extends GraphQLTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_dataproducers_test',
    'graphql_composable',
  ];

  /**
   * The GraphQL schema for this test.
   */
  protected const SCHEMA = <<<GQL
    type Mutation {
      resetPassword(mail: String!): Response
    }
    type Response {
      violations: [Violation!]!
    }
    type Violation {
      message: String!
    }
  GQL;

  /**
   * A GraphQL query to reset a user's password.
   */
  protected const QUERY = <<<GQL
    mutation ResetPassword(\$mail: String!) {
      resetPassword(mail: \$mail) {
        violations { message }
      }
    }
  GQL;

  /**
   * Test users.
   *
   * @var array<\Drupal\user\UserInterface>
   */
  protected array $users;

  /**
   * The mail manager mock.
   */
  protected MailManagerInterface&MockObject $mailManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSchema(self::SCHEMA);

    $this->config('user.settings')
      ->set('notify.password_reset', TRUE)
      ->save();

    $this->mailManager = $this->createMock(MailManagerInterface::class);
    $this->container->set('plugin.manager.mail', $this->mailManager);

    // Create two test users.
    $this->users = [
      $this->createUser(),
      $this->createUser(),
    ];

    $this->mockResolver('Mutation', 'resetPassword',
      $this->builder->produce('password_reset')
        ->map('email', $this->builder->fromArgument('mail')),
    );
    $this->mockResolver('Response', 'violations',
      $this->builder->produce('response_violations')
        ->map('response', $this->builder->fromParent()),
    );
  }

  /**
   * Test email not existing is not revealed.
   */
  public function testNonExistentEmailReturnsNoViolations(): void {
    $this->mailManager
      ->expects($this->never())
      ->method('mail');

    $this->assertResults(
      self::QUERY,
      ['mail' => 'nonexistent@example.com'],
      [
        'resetPassword' => [
          'violations' => [],
        ],
      ],
      $this->defaultMutationCacheMetaData(),
    );
  }

  /**
   * Test email existing is not revealed.
   */
  public function testValidMailResetsPassword(): void {
    $this->mailManager
      ->method('mail')
      ->willReturn(['result' => TRUE]);

    $this->assertResults(
      self::QUERY,
      ['mail' => $this->users[1]->getEmail()],
      [
        'resetPassword' => [
          'violations' => [],
        ],
      ],
      $this->defaultMutationCacheMetaData(),
    );
  }

  /**
   * Test email failure is reported as violation.
   */
  public function testFailedSendProvidesViolation(): void {
    $this->mailManager
      ->method('mail')
      ->willReturn(['result' => FALSE]);

    $this->assertResults(
      self::QUERY,
      ['mail' => $this->users[1]->getEmail()],
      [
        'resetPassword' => [
          'violations' => [
            ['message' => 'Unable to reset password, please try again later.'],
          ],
        ],
      ],
      $this->defaultMutationCacheMetaData(),
    );
  }

}
