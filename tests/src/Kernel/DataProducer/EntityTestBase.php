<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Shared fixtures for entity data producer tests.
 */
abstract class EntityTestBase extends GraphQLTestBase {

  /**
   * Test node.
   */
  protected NodeInterface $node;

  /**
   * Mocked test entity.
   */
  protected NodeInterface|MockObject $entity;

  /**
   * Mocked test entity interface.
   */
  protected EntityInterface $entityInterface;

  /**
   * Mocked test user.
   */
  protected UserInterface $user;

  /**
   * Translated test entity.
   */
  protected NodeInterface $translationFr;

  /**
   * Translated test entity.
   */
  protected NodeInterface $translationDe;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->entity = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityInterface = $this->getMockBuilder(EntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->user = $this->getMockBuilder(UserInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $content_type = NodeType::create([
      'type' => 'lorem',
      'name' => 'ipsum',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    // cspell:ignore otherbundle
    $content_type = NodeType::create([
      'type' => 'otherbundle',
      'name' => 'otherbundle',
      'translatable' => TRUE,
      'display_submitted' => FALSE,
    ]);
    $content_type->save();

    $this->node = Node::create([
      'title' => 'Dolor',
      'type' => 'lorem',
    ]);
    $this->node->save();

    $this->translationFr = $this->node->addTranslation('fr', ['title' => 'sit amet fr']);
    $this->translationFr->save();

    $this->translationDe = $this->node->addTranslation('de', ['title' => 'sit amet de']);
    $this->translationDe->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'lorem', TRUE);
  }

}
