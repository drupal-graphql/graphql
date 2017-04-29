<?php

namespace Drupal\graphql_block;

use Symfony\Component\HttpFoundation\Response;

/**
 * Kernel response containing block objects.
 */
class BlockResponse extends Response {

  /**
   * The retrieved context.
   *
   * @var \Drupal\Block\Entity\Block[]
   */
  protected $blocks;

  /**
   * Set the list of blocks.
   *
   * @param \Drupal\Block\Entity\Block[] $blocks
   *   The context object.
   */
  public function setBlocks(array $blocks) {
    $this->blocks = $blocks;
  }

  /**
   * Retrieve the context object.
   *
   * @return \Drupal\Block\Entity\Block[]
   *   The contained context object.
   */
  public function getBlocks() {
    return $this->blocks;
  }

}
