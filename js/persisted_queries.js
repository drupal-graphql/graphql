/**
 * @file
 * Attaches the show/hide functionality to checkboxes in the "Persisted queries"
 * form.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.persistedQueries = {
    attach: function (context, settings) {
      $('.persisted-queries-enabled-wrapper input.form-checkbox', context).each(function () {
        var $checkbox = $(this);
        var plugin_id = $checkbox.data('id');

        var $rows = $('.persisted-queries-weight--' + plugin_id, context);
        var tab = $('.persisted-queries-settings--' + plugin_id, context).data('verticalTab');

        // Bind a click handler to this checkbox to conditionally show and hide
        // the processor's table row and vertical tab pane.
        $checkbox.on('click.persistedQueryUpdate', function () {
          if ($checkbox.is(':checked')) {
            $rows.show();
            if (tab) {
              tab.tabShow().updateSummary();
            }
          }
          else {
            $rows.hide();
            if (tab) {
              tab.tabHide().updateSummary();
            }
          }
        });

        // Attach summary for configurable items (only for screen-readers).
        /*if (tab) {
          tab.details.drupalSetSummary(function () {
            return $checkbox.is(':checked') ? Drupal.t('Enabled') : Drupal.t('Disabled');
          });
        }*/

        // Trigger our bound click handler to update elements to initial state.
        $checkbox.triggerHandler('click.persistedQueryUpdate');
      });
    }
  };

})(jQuery);
