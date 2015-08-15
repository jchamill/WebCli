define([
  'underscore',
  'backbone'
], function(_, Backbone) {
  var CommandModel = Backbone.Model.extend({
    defaults: {
      command: 'command not found'
    },
    initialize: function() {

    }
  });
  return CommandModel;
});