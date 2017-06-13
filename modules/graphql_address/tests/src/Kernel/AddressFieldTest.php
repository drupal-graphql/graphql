<?php

namespace Drupal\Tests\graphql_address\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test retrieving address field in GraphQL.
 *
 * @requires module address
 * @group graphql_address
 */
class AddressFieldTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'address',
    'graphql_content',
    'graphql_address',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('node');
    $this->installConfig('filter');
    $this->installConfig('address');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    FieldStorageConfig::create([
      'field_name' => 'address',
      'type' => 'address',
      'entity_type' => 'node',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'address',
      'entity_type' => 'node',
      'bundle' => 'test',
      'label' => 'address',
    ])->save();

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'test',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('address', ['type' => 'address_default'])->save();

  }

  /**
   * Test a simple address field.
   */
  public function testAddressField() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);
    // White House: 1600 Pennsylvania Ave NW, Washington, DC 20500
    $a->address = [
      'postal_code' => 20500,
      'address_line1' => '1600 Pennsylvania Ave NW',
      'address_line2' => NULL,
      'given_name' => NULL,
      'sorting_code' => NULL,
      'additional_name' => NULL,
      'dependent_locality' => NULL,
      'administrative_area' => 'DC',
      'family_name' => NULL,
      'country_code' => 'US',
      'organization' => NULL,
      'locality' => 'Washington',
    ];

    $a->save();

    $result = $this->executeQueryFile('address.gql', ['path' => '/node/' . $a->id()]);

    $address = $result['data']['route']['node']['address'];

    $this->assertEquals($address['postal_code'], 20500, 'Postal Code is correct');
    $this->assertEquals($address['address_line1'], '1600 Pennsylvania Ave NW', 'Address Line 1 is correct');
    $this->assertEquals($address['address_line2'], NULL, 'Address Line 2 is correct');
    $this->assertEquals($address['given_name'], NULL, 'Given Name is correct');
    $this->assertEquals($address['sorting_code'], NULL, 'Sorting Code is correct');
    $this->assertEquals($address['additional_name'], NULL, 'Additional Name is correct');
    $this->assertEquals($address['dependent_locality'], NULL, 'Depdendent Locality is correct');
    $this->assertEquals($address['administrative_area'], 'DC', 'Administrative Area is correct');
    $this->assertEquals($address['family_name'], NULL, 'Family Name is correct');
    $this->assertEquals($address['country_code'], 'US', 'Country Code is correct');
    $this->assertEquals($address['organization'], NULL, 'Organization is correct');
    $this->assertEquals($address['locality'], 'Washington', 'Locality is correct');
  }

}
