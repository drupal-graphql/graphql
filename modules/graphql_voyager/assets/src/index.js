import React from 'react';
import ReactDOM from 'react-dom';
import { Voyager } from 'graphql-voyager';
import Drupal from 'drupal';
import jQuery from 'jquery';

/**
 * Behavior for rendering the GraphQL Voyager interface.
 */
Drupal.behaviors.graphQLRenderVoyager = {
  attach: (context, settings) => {
    const container = jQuery('#graphql-voyager', context).once('graphql-voyager')[0] || undefined;

    if (typeof container === 'undefined') {
      return;
    }

    ReactDOM.render(<Voyager
      introspection={settings.graphqlIntrospectionData}
      displayOptions={{ skipRelay: true }}
    />, container);
  },
};
