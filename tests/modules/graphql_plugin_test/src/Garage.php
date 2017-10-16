<?php

namespace Drupal\graphql_plugin_test;

/**
 * Garage implementation.
 *
 * Dummy implementation for the sake of of a complete service definition. To be
 * replaced with prophecies in tests.
 */
class Garage implements GarageInterface {
  /**
   * The list of parked vehicles.
   *
   * @var array
   */
  protected $vehicles = [];

  /**
   * {@inheritdoc}
   */
  public function getVehicles() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function insertVehicle(array $vehicle, $lot = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getVehicle($lot) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVehicle($lot) {
    return NULL;
  }

}
