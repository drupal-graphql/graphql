import React from 'react';
import ReactDOM from 'react-dom';
import Drupal from 'drupal';
import jQuery from 'jquery';
import GraphiQL from 'graphiql';
import { buildClientSchema } from 'graphql';

/**
 * Behavior for rendering the GraphiQL interface.
 */
Drupal.behaviors.graphQLRenderExplorer = {
  attach: (context, settings) => {
    const container = jQuery('#graphql-explorer', context).once('graphql-explorer')[0] || undefined;

    if (typeof container === 'undefined') {
      return;
    }

    // Build a schema from the passed introspection data.
    const graphQLSchema = buildClientSchema(settings.graphqlIntrospectionData.data);

    // Defines a GraphQL fetcher using the fetch API.
    const graphQLFetcher = (graphQLParams) => fetch(settings.graphqlRequestUrl, {
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
        schema: graphQLSchema,
      }), container
    );
  },
};
