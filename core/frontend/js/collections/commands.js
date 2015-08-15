define([
  'jquery',
  'underscore',
  'backbone',
  'models/command'
], function($, _, Backbone, CommandModel) {
  var CommandsCollection = Backbone.Collection.extend({
    model: CommandModel,
    initialize: function() {

    }
  });
  return CommandsCollection;
});