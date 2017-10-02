<?php

namespace Drupal\graphql_twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\QueryProcessor;

trait GraphQLTemplateTrait {

  /**
   * @var QueryProcessor
   */
  protected $processor;

  public function setGraphQLProcessor(QueryProcessor $processor) {
    $this->processor = $processor;
  }

  public function render(array $variables) {
    if ($this->processor) {
      if ($query = $this->getGraphQLQuery()) {
        $vars = [];
        $source = (new \Youshido\GraphQL\Parser\Parser())->parse($query);

        foreach ($source['variables'] as $variable) {
          /** @var \Youshido\GraphQL\Parser\Ast\ArgumentValue\Variable $variable */
          $arg = $variable->getName();
          $vars[$arg] = isset($variables[$arg]) ? $variables[$arg] : NULL;
        }

        $vars = array_map(function ($value) {
          return $value instanceof EntityInterface ? $value->id() : $value;
        }, $vars);

        return parent::render($this->processor->processQuery($query, $vars)->getData());
      }
    }
    return parent::render($variables);
  }

  public function getGraphQLQuery() {

    $query = '';
    if ($this instanceof \Twig_Template) {
      if ($this->graphqlParent) {
        $query = $this->loadTemplate($this->graphqlParent)->getGraphQLQuery();
      }
      if ($this->graphqlQuery) {
        $query = $this->graphqlQuery;
      }
    }

    $includes = array_map(function ($template) {
      return $this->loadTemplate($template)->getGraphQLQuery();
    }, $this->getGraphQLIncludes());

    if ($query) {
      array_unshift($includes, $query);
    }
    return implode("\n", $includes);
  }

  public function getGraphQLIncludes() {
    $includes = $this->graphqlIncludes;
    foreach ($this->graphqlIncludes as $include) {
      $includes += $this->loadTemplate($include)->getGraphQLIncludes();
    }
    return $includes;
  }
}