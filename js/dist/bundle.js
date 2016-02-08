/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	var _react = __webpack_require__(1);

	var _react2 = _interopRequireDefault(_react);

	var _reactDom = __webpack_require__(2);

	var _reactDom2 = _interopRequireDefault(_reactDom);

	var _drupal = __webpack_require__(3);

	var _drupal2 = _interopRequireDefault(_drupal);

	var _jquery = __webpack_require__(4);

	var _jquery2 = _interopRequireDefault(_jquery);

	_drupal2['default'].behaviors.graphqlRenderExplorer = {
	  attach: function attach(context, settings) {
	    var container = (0, _jquery2['default'])('#graphql-explorer', context).once('graphql-explorer')[0] || undefined;

	    if (typeof container === 'undefined') {
	      return;
	    }

	    // Parse the search string to get url parameters.
	    var search = window.location.search;
	    var parameters = {};

	    search.substr(1).split('&').forEach(function (entry) {
	      var eq = entry.indexOf('=');

	      if (eq >= 0) {
	        parameters[decodeURIComponent(entry.slice(0, eq))] = decodeURIComponent(entry.slice(eq + 1));
	      }
	    });

	    // If variables was provided, try to format it.
	    if (parameters.variables) {
	      try {
	        parameters.variables = JSON.stringify(JSON.parse(query.variables), null, 2);
	      } catch (e) {
	        // Do nothing, we want to display the invalid JSON as a string, rather than
	        // present an error.
	      }
	    }

	    // When the query and variables string is edited, update the URL bar so that it
	    // can be easily shared.
	    function onEditQuery(newQuery) {
	      parameters.query = newQuery;
	      updateURL();
	    }

	    function onEditVariables(newVariables) {
	      parameters.variables = newVariables;
	      updateURL();
	    }

	    function updateURL() {
	      var newSearch = '?' + Object.keys(parameters).map(function (key) {
	        return encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]);
	      }).join('&');

	      history.replaceState(null, null, newSearch);
	    }

	    // Defines a GraphQL fetcher using the fetch API.
	    function graphQLFetcher(graphQLParams) {
	      return fetch(settings.graphqlRequestUrl, {
	        method: 'post',
	        credentials: 'same-origin',
	        body: JSON.stringify(graphQLParams),
	        headers: {
	          'Content-Type': 'application/json'
	        }
	      }).then(function (response) {
	        return response.json();
	      });
	    }

	    // Render <GraphiQL /> into the container.
	    _reactDom2['default'].render(_react2['default'].createElement(GraphiQL, {
	      fetcher: graphQLFetcher,
	      query: parameters.query,
	      variables: parameters.variables,
	      onEditQuery: onEditQuery,
	      onEditVariables: onEditVariables
	    }), container);
	  }
	};

/***/ },
/* 1 */
/***/ function(module, exports) {

	module.exports = React;

/***/ },
/* 2 */
/***/ function(module, exports) {

	module.exports = ReactDOM;

/***/ },
/* 3 */
/***/ function(module, exports) {

	module.exports = Drupal;

/***/ },
/* 4 */
/***/ function(module, exports) {

	module.exports = jQuery;

/***/ }
/******/ ]);