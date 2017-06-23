<?php

namespace Drupal\graphql_metatag;

use Symfony\Component\HttpFoundation\Response;

/**
 * Kernel response containing metatag information.
 */
class MetatagResponse extends Response {

  /**
   * The retrieved metatags.
   *
   * @var string[]
   */
  protected $metatags;

  /**
   * Set the metatags value.
   *
   * @param string[] $metatags
   *   The list of metatags.
   */
  public function setMetatags(array $metatags) {
    $this->metatags = $metatags;
  }

  /**
   * Retrieve the list of metatags.
   *
   * @return string[]
   *   The contained metatags list.
   */
  public function getMetatags() {
    return $this->metatags;
  }

}
