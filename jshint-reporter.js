var stylish = require("jshint-stylish");
// Weird double require here as the module exports the path to its reporter file
var junit = require(require("jshint-junit-reporter"));

exports.reporter = function(results, data, opts){
	stylish.reporter(results, data, opts);
	opts.outputFile = './test-results/jshint-junit.xml';
	junit.reporter(results, data, opts);
};
