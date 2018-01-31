/**
 * @file
 * Activates tablesorter plugin for GatherContent tables.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.gatherContentTableSorter = {
    attach: function (context) {
      if (typeof $().tablesorter === 'undefined') {
        return;
      }

      if ($.tablesorter.getParserById('data_date') === false) {
        $.tablesorter.addParser({
          id: 'data_date',
          is: function (s, table, cell, $cell) {
            return !!$cell.attr('data-date');
          },
          format: function (s, table, cell, cellIndex) {
            var $cell = $(cell);
            if ($cell.attr('data-date')) {
              return $cell.attr('data-date') || s;
            }

            return s;
          },
          parsed: false,
          type: 'text'
        });
      }

      $('table.tablesorter-enabled', context).once('gather-content-table-sorter').each(function () {
        var tablesorterOptions = {
          cssAsc: 'sort-down',
          cssDesc: 'sort-up'
        };

        if ((typeof drupalSettings.gatherContent !== 'undefined') &&
          (typeof drupalSettings.gatherContent.tableSorterOptionOverrides === 'object')
        ) {
          var tsOverrides = drupalSettings.gatherContent.tableSorterOptionOverrides;

          for (var attrName in tsOverrides) {
            if (tsOverrides.hasOwnProperty(attrName)) {
              tablesorterOptions[attrName] = tsOverrides[attrName];
            }
          }
        }

        // Keeps sticky table cell classes up-to-date.
        // Makes sticky header's tablesorter classes follow the main table's
        // ones.
        $(this).tablesorter(tablesorterOptions)
          .bind('sortEnd', function (event) {
            if ($(this).is('table + table')) {
              var $baseTable = $('table + table');
              var $stickyTable = $baseTable.prev('table.sticky-header');

              if ($baseTable.length && $stickyTable.length) {
                var $baseTableHeaderCells = $baseTable.find('thead th');
                var $stickyTableHeaderCells = $stickyTable.find('thead th');
                var baseTableHeaderNum = $baseTableHeaderCells.length;

                for (var i = 0; i < baseTableHeaderNum; i++) {
                  var baseTableHeaderCellClasses = $($baseTableHeaderCells[i]).attr('class');

                  $($stickyTableHeaderCells[i]).attr('class', baseTableHeaderCellClasses);
                }
              }
            }
          });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
