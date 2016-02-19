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

	var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

	var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

	var _react = __webpack_require__(1);

	var _react2 = _interopRequireDefault(_react);

	var _reactDom = __webpack_require__(2);

	var _reactDom2 = _interopRequireDefault(_reactDom);

	var _drupal = __webpack_require__(3);

	var _drupal2 = _interopRequireDefault(_drupal);

	var _jquery = __webpack_require__(4);

	var _jquery2 = _interopRequireDefault(_jquery);

	var _graphiql = __webpack_require__(5);

	var _graphiql2 = _interopRequireDefault(_graphiql);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

	/**
	 * Behavior for rendering the GraphiQL interface.
	 */
	_drupal2.default.behaviors.graphQLRenderExplorer = {
	  attach: function attach(context, settings) {
	    var container = (0, _jquery2.default)('#graphql-explorer', context).once('graphql-explorer')[0] || undefined;

	    if (typeof container === 'undefined') {
	      return;
	    }

	    // Parse the search string to get url parameters.
	    var search = window.location.search;
	    var parameters = search.substr(1).split('&').map(function (entry) {
	      return [entry, entry.indexOf('=')];
	    }).filter(function (_ref) {
	      var _ref2 = _slicedToArray(_ref, 2);

	      var equal = _ref2[1];
	      return !!equal;
	    }).reduce(function (previous, _ref3) {
	      var _ref4 = _slicedToArray(_ref3, 2);

	      var entry = _ref4[0];
	      var equal = _ref4[1];
	      return _extends({}, previous, _defineProperty({}, decodeURIComponent(entry.slice(0, equal)), decodeURIComponent(entry.slice(equal + 1))));
	    }, {});

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
	    var updateURL = function updateURL() {
	      var newSearch = '?' + Object.keys(parameters).map(function (key) {
	        return encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]);
	      }).join('&');

	      history.replaceState(null, null, newSearch);
	    };

	    var onEditQuery = function onEditQuery(newQuery) {
	      parameters.query = newQuery;
	      updateURL();
	    };

	    var onEditVariables = function onEditVariables(newVariables) {
	      parameters.variables = newVariables;
	      updateURL();
	    };

	    // Defines a GraphQL fetcher using the fetch API.
	    var graphQLFetcher = function graphQLFetcher(graphQLParams) {
	      return fetch(settings.graphQLRequestUrl, {
	        method: 'post',
	        credentials: 'same-origin',
	        body: JSON.stringify(graphQLParams),
	        headers: {
	          'Content-Type': 'application/json'
	        }
	      }).then(function (response) {
	        return response.json();
	      });
	    };

	    // Render <GraphiQL /> into the container.
	    _reactDom2.default.render(_react2.default.createElement(_graphiql2.default, {
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

/***/ },
/* 5 */
/***/ function(module, exports) {

	module.exports = GraphiQL;

/***/ }
/******/ ]);