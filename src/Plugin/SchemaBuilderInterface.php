<?php

namespace Drupal\graphql\Plugin;

interface SchemaBuilderInterface {

  /**
   * @return bool
   */
  public function hasFields($type);

  /**
   * @return bool
   */
  public function hasMutations();

  /**
   * @return bool
   */
  public function hasType($name);

  /**
   * @return array
   */
  public function getFields($parent);

  /**
   * @return array
   */
  public function getMutations();

  /**
   * @return array
   */
  public function getTypes();

  /**
   * @param $name
   *
   * @return mixed
   */
  public function getType($name);

  /**
   * Retrieve the list of derivatives associated with a composite type.
   *
   * @return string[]
   *   The list of possible sub typenames.
   */
  public function getSubTypes($name);

  /**
   * Resolve the matching type.
   */
  public function resolveType($name, $value, $context, $info);

  /**
   * @param $mutations
   *
   * @return array
   */
  public function processMutations($mutations);

  /**
   * @param $fields
   *
   * @return array
   */
  public function processFields($fields);

  /**
   * @param $args
   *
   * @return array
   */
  public function processArguments($args);

  /**
   * @param $type
   *
   * @return mixed
   */
  public function processType($type);
}