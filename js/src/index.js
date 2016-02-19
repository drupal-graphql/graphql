import React from 'react';
import ReactDOM from 'react-dom';
import Drupal from 'drupal';
import jQuery from 'jquery';
import GraphiQL from 'graphiql';

/**
 * Behavior for rendering the GraphiQL interface.
 */
Drupal.behaviors.graphQLRenderExplorer = {
  attach: (context, settings) => {
    const container = jQuery('#graphql-explorer', context).once('graphql-explorer')[0] || undefined;

    if (typeof container === 'undefined') {
      return;
    }

    // Parse the search string to get url parameters.
    const search = window.location.search;
    const parameters = search.substr(1).split('&')
      .map((entry) => ([entry, entry.indexOf('=')]))
      .filter(([, equal]) => !!equal)
      .reduce((previous, [entry, equal]) => ({
        ...previous,
        [decodeURIComponent(entry.slice(0, equal))]: decodeURIComponent(entry.slice(equal + 1)),
      }), {});

    // If variables was provided, try to format it.
    if (parameters.variables) {
      try {
        parameters.variables = JSON.stringify(JSON.parse(parameters.variables), null, 2);
      } catch (e) {
        // Do nothing, we want to display the invalid JSON as a string, rather
        // than present an error.
      }
    }

    // When the query and variables string is edited, update the URL bar so that
    // it can be easily shared.
    const updateURL = () => {
      const newSearch = `?${Object.keys(parameters).map(
        (key) => `${encodeURIComponent(key)}=${encodeURIComponent(parameters[key])}`
      ).join('&')}`;

      history.replaceState(null, null, newSearch);
    };

    const onEditQuery = (newQuery) => {
      parameters.query = newQuery;
      updateURL();
    };

    const onEditVariables = (newVariables) => {
      parameters.variables = newVariables;
      updateURL();
    };

    // Defines a GraphQL fetcher using the fetch API.
    const graphQLFetcher = (graphQLParams) => fetch(settings.graphQLRequestUrl, {
      method: 'post',
      credentials: 'same-origin',
      body: JSON.stringify(graphQLParams),
      headers: {
        'Content-Type': 'application/json',
      },
    }).then((response) => response.json());

    // Render <GraphiQL /> into the container.
    ReactDOM.render(
      React.createElement(GraphiQL, {
        fetcher: graphQLFetcher,
        query: parameters.query,
        variables: parameters.variables,
        onEditQuery,
        onEditVariables,
      }), container
    );
  },
};
