(function(factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(window.jQuery);
  }
}(function($) {


  $.extend($.summernote.plugins, {
    /**
     * @param {Object} context - context object has status of editor.
     */
    'snfinder': function(context) {
      var self = this;


      var ui = $.summernote.ui;

      context.memo('button.snfinder', function() {
        var button = ui.button({
          contents: '<i class="fa fa-image"/> 图片',
          tooltip: 'ZAP 文件管理器',
          container: '.note-editor.note-frame',
          click: function(e) {
            Zap.finder({callback:'insertEditor',context:context});
            // context.invoke('editor.insertText', 'hello');
          },
        });

        var $snfinder = button.render();
        return $snfinder;
      });

      // This events will be attached when editor is initialized.
      this.events = {
      };

      this.initialize = function() {

      };

      this.destroy = function() {

      };
    },
  });
}));
function insertEditor(e){
  window.zapFinder.settings.context.invoke('editor.insertImage', $(e.target).data('original') , $(e.target).attr('alt'));
}