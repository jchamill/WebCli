define([
  'underscore',
  'backbone'
], function(_, Backbone) {
  var ConfigModel = Backbone.Model.extend({
    url: 'core/backend/backbone/config.php',
    defaults: {
      welcomeMsg: 'Connected!'
    }
  });
  return ConfigModel;
});