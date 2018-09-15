import webpack from 'webpack';
import path from 'path';
import CopyWebpackPlugin from 'copy-webpack-plugin';

module.exports = {
  context: path.resolve(__dirname, 'src'),
  entry: './index.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'bundle.min.js',
  },
  resolve: {
    extensions: ['.jsx', '.js', '.json'],
    modules: [
      path.resolve(__dirname, 'node_modules'),
      'node_modules',
    ],
  },

  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: 'babel-loader',
      },
    ],
  },
  plugins: ([
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
    }),
    new CopyWebpackPlugin([
      { from: path.resolve(__dirname, 'node_modules/graphql-voyager/dist/voyager.worker.js') },
      { from: path.resolve(__dirname, 'node_modules/graphql-voyager/dist/voyager.css') },
      { from: path.resolve(__dirname, 'node_modules/graphql-voyager/dist/voyager.css.map') },
      { from: path.resolve(__dirname, 'src/container.css') },
    ]),
  ]).concat(process.env.NODE_ENV === 'production' ? [
    new webpack.optimize.UglifyJsPlugin({
      output: {
        comments: false,
      },
      compress: {
        unsafe_comps: true,
        properties: true,
        keep_fargs: false,
        pure_getters: true,
        collapse_vars: true,
        unsafe: true,
        warnings: false,
        screw_ie8: true,
        sequences: true,
        dead_code: true,
        drop_debugger: true,
        comparisons: true,
        conditionals: true,
        evaluate: true,
        booleans: true,
        loops: true,
        unused: true,
        hoist_funs: true,
        if_return: true,
        join_vars: true,
        cascade: true,
        drop_console: true,
      },
    }),
  ] : []),
  externals: {
    jquery: 'jQuery',
    drupal: 'Drupal',
  },
};
