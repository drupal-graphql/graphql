<?php

namespace Drupal\Tests\graphql_rules\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Entity\RulesComponentConfig;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;

/**
 * Test rules support in GraphQL.
 *
 * @group graphql_rules
 */
class ViewsTest extends GraphQLFileTestBase {

  /**
   * The expression plugin manager.
   *
   * @var \Drupal\rules\Engine\ExpressionManager
   */
  protected $expressionManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rules',
    'graphql_rules',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->expressionManager = $this->container->get('plugin.manager.rules_expression');
  }

  /**
   * Tests rules integration.
   */
  public function testRules() {
    $nested_rule = $this->expressionManager->createRule();
    // Create a node entity with the action.
    $nested_rule->addAction('rules_entity_create:node', ContextConfig::create()
      ->setValue('type', 'page')
    );
    // Set the title of the new node so that it is marked for auto-saving.
    $nested_rule->addAction('rules_data_set', ContextConfig::create()
      ->map('data', 'entity.title')
      ->setValue('value', 'new title')
    );
    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($nested_rule);
    $rules_config->setProvidedContextDefinitions(['concatenated' => ContextDefinition::create('string')]);
    $rules_config->save();

    $this->executeQueryFile('basic');
    $this->assertEquals(TRUE, TRUE);
  }

}
