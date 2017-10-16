<?php

namespace Drupal\graphql_plugin_test;

/**
 * Interface definition for a garage.
 *
 * Used for testing GraphQL queries and mutations.
 */
interface GarageInterface {

  /**
   * Retrieve a list of vehicles.
   *
   * @return mixed
   *   The list of vehicles, parked in the garage.
   */
  public function getVehicles();

  /**
   * Add a vehicle to your garage.
   *
   * @param array $vehicle
   *   The vehicles properties.
   * @param int $lot
   *   The parking lot.
   *
   * @return int
   *   The number of the parking lot.
   */
  public function insertVehicle(array $vehicle, $lot = NULL);

  /**
   * Get vehicle by parking lot.
   *
   * @param int $lot
   *   The parking lot.
   *
   * @return array
   *   The vehicle definition.
   */
  public function getVehicle($lot);

  /**
   * Remove a vehicle from the garage.
   *
   * @param int $lot
   *   The parking lot.
   *
   * @return array
   *   The removed vehicle.
   */
  public function removeVehicle($lot);

}
