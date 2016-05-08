<?php

namespace Drupal\graphql\Tests;

/**
 * Test fetching lists of entities through entity queries.
 *
 * @group graphql
 */
class EntityQueryTest extends QueryTestBase {
  /**
   * Helper function to issue a HTTP request with simpletest's cURL.
   *
   * @return string
   *   The content returned from the request.
   */
  public function testSingleNodeQuery() {
    $content = $this->randomMachineName(32);
    $node = $this->createNode([
      'type' => 'article',
      'body' => [[
        'value' => $content,
        'format' => filter_default_format(),
      ]],
    ]);

    $nodeId = $node->id();
    $nodeTitle = $node->getTitle();
    $author = $node->getOwner();
    $authorName = $author->getAccountName();

    $query = "{
      node(id: {$nodeId}) {
        nid
        title
        uid {
          entity {
            name
            langcode {
              language {
                name
              }
            }
          }
        }

        ... on EntityNodeArticle {
          body {
            value
          }
        }
      }
    }";

    $expected = [
      'data' => [
        'node' => [
          'nid' => (int) $nodeId,
          'title' => $nodeTitle,
          'uid' => [
            'entity' => [
              'name' => $authorName,
              'langcode' => [
                'language' => [
                  'name' => 'English',
                ],
              ],
            ],
          ],
          'body' => [
            'value' => $content,
          ],
        ],
      ],
    ];

    $this->assertResponseBody($expected, $this->query($query));
  }
}
