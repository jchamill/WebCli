define([
  'jquery',
  'underscore',
  'backbone',
  'models/config'
], function($, _, Backbone, ConfigModel) {
  var AppRouter = Backbone.Router.extend({
    routes: {
      '*actions': 'defaultAction'
    }
  });

  var initialize = function() {
    var app_router = new AppRouter();
    app_router.on('route:defaultAction', function(actions) {
      require(['views/home/main'], function(MainHomeView) {
        // use the ConfigModel to get configured welcome message
        var configModel = new ConfigModel();
        configModel.fetch({
          success: function(e) {
            var mainHomeView = new MainHomeView();
            mainHomeView.render(e.attributes.welcomeMsg);
          }
        });
      });
    });
    Backbone.history.start();
  };

  return {
    initialize: initialize
  };
});