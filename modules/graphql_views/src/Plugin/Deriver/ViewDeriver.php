<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\views\Views;

/**
 * Derive fields from configured views.
 */
class ViewDeriver extends ViewDeriverBase implements ContainerDeriverInterface {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $viewStorage = $this->entityTypeManager->getStorage('view');

    foreach (Views::getApplicableViews('graphql_display') as list($viewId, $displayId)) {
      /** @var \Drupal\views\ViewEntityInterface $view */
      $view = $viewStorage->load($viewId);
      $display = $this->getViewDisplay($view, $displayId);

      if (!$type = $this->getEntityTypeByTable($view->get('base_table'))) {
        // Skip for now, switch to different response type later when
        // implementing fieldable views display support.
        continue;
      }

      $contextualSets = $this->getContextualSets($display->getOption('arguments') ?: []);
      if (empty($contextualSets)) {
        // There are no types we can attach to.
        continue;
      }

      $id = implode('-', [$viewId, $displayId, 'view']);

      $typeName = graphql_core_camelcase($type);
      $multi = TRUE;
      $paged = FALSE;
      $arguments = [];

      $filters = array_filter($display->getOption('filters') ?: [], function ($filter) {
        return $filter['exposed'];
      });

      if ($filters) {
        $arguments['filter'] = [
          'type' => graphql_core_camelcase([
            $viewId, $displayId, 'view', 'filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];
      }


      $sorts = array_filter($display->getOption('sorts') ?: [], function ($sort) {
        return $sort['exposed'];
      });

      if ($sorts) {
        $arguments += [
          'sortDirection' => [
            "name" => "sortDirection",
            "type" => [
              "ASC" => "Ascending",
              "DESC" => "Descending",
            ],
            "default" => TRUE,
          ],
          'sortBy' => [
            "name" => "sortBy",
            "type" => array_map(function ($sort) {
              return $sort['expose']['label'];
            }, $sorts),
            "nullable" => TRUE,
          ],
        ];
      }

      if (!$this->interfaceExists($typeName)) {
        $typeName = 'Entity';
      }

      // If a pager is configured we apply the matching ViewResult derivative
      // instead of the entity list.
      if ($this->isPaged($display)) {
        $typeName = graphql_core_camelcase(implode('-', [
          $viewId, $displayId, 'result',
        ]));
        $multi = FALSE;
        $paged = TRUE;
        $arguments += [
          'page' => ['type' => 'Int', 'default' => $this->getPagerOffset($display)],
          'pageSize' => ['type' => 'Int', 'default' => $this->getPagerLimit($display)],
        ];
      }

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => graphql_core_propcase($id),
        'types' => call_user_func_array('array_merge', array_map(function($set) {
          return $set['graphql_types'];
        }, $contextualSets)),
        'contextual_sets' => $contextualSets,
        'type' => $typeName,
        'multi' => $multi,
        'arguments' => $arguments,
        'view' => $viewId,
        'display' => $displayId,
        'paged' => $paged,
        'cache_tags' => $view->getCacheTags(),
        'cache_contexts' => $view->getCacheContexts(),
        'cache_max_age' => $view->getCacheMaxAge(),
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Returns contextual sets based on the view arguments.
   *
   * @param array $viewArguments
   *   The "arguments" option of a view display.
   *
   * @return array
   *   An array of sets describing use cases of the view display. Element keys:
   *     - graphql_types: (mandatory) an array of suitable GraphQL types.
   *     - argument: (optional) the ID of the view argument (contextual filter).
   *     - argument_entity_class: (optional) the class name of an entity type
   *       which is accepted by the view argument.
   */
  protected function getContextualSets(array $viewArguments) {
    $result = [];

    $mandatoryArguments = [];
    $entityIdArguments = [];
    foreach ($viewArguments as $argumentId => $argument) {
      if ($argument['default_action'] !== 'default') {
        $mandatoryArguments[] = $argumentId;
      }
      if (isset($argument['entity_type']) && isset($argument['entity_field'])) {
        $entityType = $this->entityTypeManager->getDefinition($argument['entity_type']);
        if ($entityType) {
          $idField = $entityType->getKey('id');
          if ($idField === $argument['entity_field']) {
            $entityIdArguments[] = $argumentId;
          }
        }
      }
    }

    // For now we expose views as fields on
    //   - top level, if a view have no mandatory arguments;
    //   - entity or bundle, if a view have an entity ID argument, but only in
    //     case if there is no other mandatory arguments.
    // Where "mandatory argument" stands for an argument having no default
    // value.
    if (empty($mandatoryArguments)) {
      $result[] = [
        'graphql_types' => ['Root'],
      ];
    }
    foreach ($entityIdArguments as $entityIdArgument) {
      if (!array_diff($mandatoryArguments, [$entityIdArgument])) {
        $argument = $viewArguments[$entityIdArgument];
        // Here we specify types managed by the graphql_content module, yet
        // we don't define the module as a dependency. If types are not in the
        // schema, the resulting GraphQL field will not go to the schema as
        // well.
        $types = [];
        if (
          $argument['specify_validation'] &&
          strpos($argument['validate']['type'], 'entity:') === 0 &&
          !empty($argument['validate_options']['bundles'])
        ) {
          foreach ($argument['validate_options']['bundles'] as $bundle) {
            $types[] = graphql_core_camelcase([$argument['entity_type'], $bundle]);
          }
        }
        else {
          $types[] = graphql_core_camelcase($argument['entity_type']);
        }
        $result[] = [
          'argument' => $entityIdArgument,
          'argument_entity_class' => $this->entityTypeManager->getDefinition($argument['entity_type'])->getClass(),
          'graphql_types' => $types,
        ];
      }
    }

    return $result;
  }

}
