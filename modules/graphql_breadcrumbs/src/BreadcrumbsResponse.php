<?php

namespace Drupal\graphql_breadcrumbs;

use Symfony\Component\HttpFoundation\Response;

/**
 * Kernel response containing breadcrumbs.
 */
class BreadcrumbsResponse extends Response {

  /**
   * The retrieved breadcrumbs.
   *
   * @var \Drupal\Core\Url[]
   */
  protected $breadcrumbs;

  /**
   * Set the breadcrumbs value.
   *
   * @param \Drupal\Core\Url[] $breadcrumbs
   *   The list of breadcrumbs.
   */
  public function setBreadcrumbs(array $breadcrumbs) {
    $this->breadcrumbs = $breadcrumbs;
  }

  /**
   * Retrieve the list of breadcrumb links.
   *
   * @return \Drupal\Core\Url[]
   *   The contained breadcrumbs list.
   */
  public function getBreadcrumbs() {
    return $this->breadcrumbs;
  }

}
