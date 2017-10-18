<?php

namespace Drupal\Tests\graphql_core\Traits;

use Drupal\node\NodeInterface;

/**
 * Helper methods associated with revisions.
 */
trait RevisionsTestTrait {

  /**
   * Returns a new, unpublished draft of given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return \Drupal\node\NodeInterface
   */
  protected function getNewDraft(NodeInterface $node) {
    $node->setNewRevision();
    $node->isDefaultRevision(FALSE);
    $node->setPublished(FALSE);
    return $node;
  }

}
