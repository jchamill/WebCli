define([
  'jquery',
  'underscore',
  'backbone',
  'collections/commands',
  'text!templates/home/main.html'
], function($, _, Backbone, CommandsCollection, mainHomeTemplate) {
  var MainHomeView = Backbone.View.extend({
    el: $('#page'),
    cmdHistoryIndex: 0,
    cmdState: 'initialized',
    prevCmdState: '',
    args: [],

    events: {
      'keypress #command': 'execute',
      'keyup #command': 'handleInput',
      'click #window': 'focus',
      'blur #command': 'blur'
    },

    initialize: function() {
      this.collection = new CommandsCollection();

      this.blinkInterval = null;
    },

    render: function(welcomeMsg) {
      this.$el.html(mainHomeTemplate);

      this.$('#welcome-msg').html(welcomeMsg);
      this.input = this.$('#command');
      this.terminal = this.$('#terminal');
      this.text = this.$('#text');
      this.cursor = this.$('#cursor');

      this.focus();
    },

    makeRequest: function(input) {
      var output = '',
        thiz = this,
        data;

      data = {
        input: input,
        state: this.cmdState
      };

      // modify the data if we are reading arguments
      if (this.cmdState == 'readingArguments') {
        // if we are reading arguments, the input is actually the argument
        this.args.push(input);
        // we need to set the original command since our backend is stateless
        data['input'] = this.originalCommand;
        // pass the args that were read from the terminal
        data['args'] = this.args;
      }

      // send the request to the backend
      $.ajax({
        url: 'core/backend/index.php',
        type: 'post',
        async: false, // block input to mimic a real terminal, could update to use a loading icon though
        data: data,
        dataType: 'json',
        error: function(data) {
          //error code
          output = data.responseText;
        },
        success: function(data) {
          //update the command state
          thiz.prevCmdState = thiz.cmdState;
          thiz.cmdState = data.commandState;
          //mask input
          if (data.masked) {
            thiz.input.prop('type', 'password');
          } else {
            thiz.input.prop('type', 'text');
          }
          //if a callback is defined, call it
          if (data.callback) {
            thiz[data.callback].apply(thiz, data.callbackArgs);
          }
          output = data.responseText;

          // if the command was completed, clear arguments
          if (data.commandState == 'completed') {
            thiz.args = [];
          }
        }
      });

      //don't show command if we are reading arguments
      if (this.prevCmdState != 'readingArguments') {
        // add command to history, notice we are not adding arguments to command history
        this.collection.add({command: input});

        this.terminal.append('<div class="command">&gt; ' + input + '</div>');
      }
      if (!_.isEmpty(output)) {
        this.terminal.append('<div class="result">' + output + '</div>');
      }
    },

    focus: function() {
      this.input.focus();
      var thiz = this;
      this.blinkInterval = setInterval(function() {
        thiz.cursor.toggleClass('blink');
      }, 500);
    },

    blur: function() {
      this.cursor.addClass('blink');
      clearInterval(this.blinkInterval);
    },

    execute: function(e) {
      if (e.keyCode != 13) return;
      if (!this.input.val()) return;

      var input = this.input.val();
      this.makeRequest(input);
      //reset history
      this.cmdHistoryIndex = 0;
      //clear input
      this.text.html(this.getCursorPrefix());
      this.input.val('');
      $('#window').animate({scrollTop: $('#window').prop('scrollHeight')});
    },

    handleInput: function(e) {
      if (e.keyCode == 38) {
        //handle up key - command history
        if (this.collection.length - (this.cmdHistoryIndex + 1) >= 0) {
          var history_cmd = this.collection.at(this.collection.length - ++this.cmdHistoryIndex);
          this.input.val(history_cmd.attributes.command);
        }
      } else if (e.keyCode == 40) {
        //handle down key - command history
        if (this.collection.length - (this.cmdHistoryIndex - 1) < this.collection.length) {
          var history_cmd = this.collection.at(this.collection.length - --this.cmdHistoryIndex);
          this.input.val(history_cmd.attributes.command);
        }
      }

      //mask text if input type is password
      var text = (this.input.prop('type') == 'password') ? this.mask(this.input.val()) : this.input.val();
      //escape html entities using jquery
      this.text.text(this.getCursorPrefix() + text).html();
    },

    mask: function(text) {
      return text.replace(/./gi, '*');
    },

    getCursorPrefix: function() {
      return (this.cmdState == 'readingArguments') ? '' : '> ';
    },

    /**
     * Used as a callback from the backend to read data from the terminal to
     * be used in the command.
     *
     * @param originalCommand
     * @param previousArgs
     */
    readFromPrompt: function(originalCommand, previousArgs) {
      this.originalCommand = originalCommand;
      this.args = previousArgs;
    },

    /**
     * @deprecated this should be a callback function in an external command
     */
    clearTerminal: function() {
      this.terminal.html('');
    }

  });

  return MainHomeView;
});