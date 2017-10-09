<?php

namespace Drupal\graphql_twig;

/**
 * Trait that will be attached to all GraphQL enabled Twig templates.
 */
trait GraphQLTemplateTrait {

  /**
   * Recursively build the GraphQL query.
   *
   * Builds the templates GraphQL query by iterating through all included or
   * embedded templates recursively.
   */
  public function getGraphQLQuery() {

    $query = '';
    $includes = [];

    if ($this instanceof \Twig_Template) {
      /** @var \Twig_Template $parent */
      $parent = $this->graphqlParent ? $this->loadTemplate($this->graphqlParent) : NULL;

      // If there is no query for this template, try to get one from the
      // parent template.
      if ($this->graphqlQuery) {
        $query = $this->graphqlQuery;
      }
      elseif ($parent) {
        $query = $parent->getGraphQLQuery();
      }

      // Recursively collect all included fragments.
      $includes = array_map(function ($template) {
        return $this->loadTemplate($template)->getGraphQLQuery();
      }, $this->getGraphQLIncludes());

      // Always add includes from parent templates.
      if ($parent) {
        $includes += array_map(function ($template) {
          return $this->loadTemplate($template)->getGraphQLQuery();
        }, $parent->getGraphQLIncludes());
      }
    }


    return implode("\n", [-1 => $query] + $includes);
  }

  /**
   * Retrieve a list of all direct or indirect included templates.
   *
   * @return string[]
   *   The list of included templates.
   */
  public function getGraphQLIncludes() {
    $includes = $this->graphqlIncludes;
    foreach ($this->graphqlIncludes as $include) {
      $includes += $this->loadTemplate($include)->getGraphQLIncludes();
    }
    return $includes;
  }
}