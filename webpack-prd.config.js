const webpackMerge = require("webpack-merge");
const commonConfig = require("./webpack.config.js");

module.exports = webpackMerge(commonConfig, {
  mode: "production",
  devtool: "source-map",
});
