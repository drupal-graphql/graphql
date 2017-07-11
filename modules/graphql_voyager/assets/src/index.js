import React from 'react';
import ReactDOM from 'react-dom';
import { Voyager } from 'graphql-voyager';
import fetch from 'isomorphic-fetch';
import Drupal from 'drupal';
import jQuery from 'jquery';

/**
 * Behavior for rendering the GraphQL Voyager interface.
 */
Drupal.behaviors.graphQLRenderVoyager = {
  attach: (context, settings) => {
    const container = jQuery('#graphql-voyager', context).once('graphql-voyager')[0] || undefined;
    const INTROSPECTION_URL = settings.graphQLRequestUrl || `${window.location.origin}/graphql`;

    if (typeof container === 'undefined') {
      return;
    }
    function introspectionProvider(query) {
      return fetch(INTROSPECTION_URL, {
        method: 'post',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query }),
        credentials: 'include',
      }).then(response => response.json());
    }

    ReactDOM.render(<Voyager introspection={introspectionProvider} displayOptions={{ skipRelay: true }}/>, container);
  },
};
