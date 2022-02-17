// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

(function ($) {
  window.block_exacomp.reload_action = function () {
    // is there a sbumit button?
    if ($('#assign-competencies input[type="submit"]').length) {
      i_want_my_reload = true;
      $('#assign-competencies input[type="submit"]').click();
    } else {
      document.location.reload(true);
    }

  }

  $(function () {
    var group = block_exacomp.get_param('group');
    var group2 = block_exacomp.get_param('colgroupid');
    if (group2 !== null) {
      group = -1; // if the JS is disabled for performance issues
    }
    block_exacomp.onlyShowColumnGroup(group);
  });
  $(document).on('click', '.colgroup-button', function () {
    block_exacomp.onlyShowColumnGroup(this.getAttribute('exa-groupid'));
    return false;
  });
  window.block_exacomp.onlyShowColumnGroup = function (group) {
    // block_exacomp.current_colgroup_id = group;
    if (group == -2) {
      $('.colgroup').not('.colgroup-5555').hide();
    }
    if (!group) {
      group = 0;
      $('.colgroup').not('.colgroup-0').hide();
      $('.colgroup-0').show();
    } else if (group == -1) {
      // show all
      $('.colgroup').show();
    } else {
      $('.colgroup').not('.colgroup-' + group).hide();
      $('.colgroup-' + group).show();
    }

    //chage form
    $('#assign-competencies').attr('action', function (i, value) {
      //if group is contained -> change value
      if (value.indexOf("group") > -1) {
        value = value.substr(0, value.indexOf("group") + 6);
        return value + group;
      }
      return value + "&group=" + group;
    });

    // change url
    history.replaceState(null, null, location.href.replace(/&group=[^&]*/i, '') + "&group=" + group);

    $('.colgroup-button').css('font-weight', 'normal');
    $('.colgroup-button[exa-groupid=' + (group * 1) + ']').css('font-weight', 'bold');
  }

  // update same examples: checkboxes (bewertungsdimensionen == 1)
  $(document).on('click', 'input[name^=dataexamples]', function () {
    var $this = $(this);
    $('input[name="' + $this.attr("name") + '"]').prop('checked', $this.prop('checked'));
  });
  // update same examples: selects (bewertungsdimensionen > 1)
  $(document).on('change', 'select[name^=dataexamples]', function () {
    var $this = $(this);
    $('select[name="' + $this.attr("name") + '"]').val($this.val());
  });
  $(document).on('change', 'input[name^=dataexamples]', function () {
    var $this = $(this);
    $('input[name="' + $this.attr("name") + '"]').val($this.val());
  });
  $(document).on('change', 'select[name^=niveau_examples-]', function () {
    var $this = $(this);
    $('select[name="' + $this.attr("name") + '"]').val($this.val());
  });

  $(function () {
    function additionlgrading_int_val(value, add) {
      value += ''; // to string
      value = value.replace(/[^0-9]/g, '');
      if (value != '') {
        value = parseInt(value);
        if (value > 100) {
          value = 100;
        }
        if (add) {
          value += ' %';
        }
      }
      return value;
    }

    $(document).on('mousedown', function () {
      // remove old dialog
      $('#exa-additionalgrading-dialog').remove();
    });

    $('input.percent-rating')
      .inputmask("9{1,3} %")
      .change(function () {
        this.value = additionlgrading_int_val(this.value, true);
      })
      .click(function () {
        // remove old dialog
        $('#exa-additionalgrading-dialog').remove();

        $(this).parent().css('position', 'relative');
        $('<div id="exa-additionalgrading-dialog" style="position: absolute; z-index: 10000; top: 34px; right: 0; width: 120px; padding: 6px 8px; background: white; border: 1px solid black;"></div>')
          .appendTo($(this).parent())
          .mousedown(function (e) { /* prevent from bubbling and closing again */
            e.stopPropagation();
            return true;
          });

        var sid = $(this).attr('id').split("-")[1];
        var eid = $(this).attr('id').split("-")[2];
        var did = $(this).attr('id').split("-")[3];

        var input = $(this);
        var allInputs = $('input[id^=additionalinfo\-' + sid + '\-' + eid + ']');

        $('<div />').appendTo('#exa-additionalgrading-dialog').slider({
          min: 0,
          max: 100,
          range: "min",
          value: additionlgrading_int_val(this.value),
          slide: function (event, ui) {
            allInputs.val(additionlgrading_int_val(ui.value, '%'));
            input.trigger("change");
          }
        });
      });
  });
})(jQueryExacomp);
