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

var formunsaved = false;
!function () {

  window.jQueryExacomp = jQuery;
  var $ = jQuery;

  Storage.prototype.setObject = function (key, value) {
    this.setItem(key, JSON.stringify(value));
  };
  Storage.prototype.getObject = function (key) {
    var value = this.getItem(key);
    return value && JSON.parse(value);
  };

  function is_page(page) {
    return !!document.location.href.match('/exacomp/' + page);
  }

  window.block_exacomp = $E = {

    exacomp_config: {
      'grade_limit': 6, // default value
    },

    set_exacomp_config: function (params, config) {
      $E.exacomp_config.grade_limit = config.grade_limit;
    },

    get_param: function (name) {
      name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
      var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);

      return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
    },

    get_location: function (params) {
      var url = document.location.href;

      $.each(params, function (name, value) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&](" + name + "=([^&#]*))"),
          results = regex.exec(location.search);

        if (results === null) {
          url += (url.indexOf('?') != -1 ? '&' : '?') + name + '=' + encodeURIComponent(value);
        } else {
          url = url.replace(results[1], name + '=' + encodeURIComponent(value));
        }
      });

      return url;
    },

    set_location_params: function (params) {
      document.location.href = this.get_location(params);
    },

    get_studentid: function () {
      studentid = $E.get_param('studentid');

      if (studentid === null || studentid == -5)
        studentid = $("#menuexacomp_competence_grid_select_student").val();

      return studentid;
    },

    call_ajax: function (data) {
      if (data.courseid == null) {
        data.courseid = $E.get_param('courseid');
      }
      data.sesskey = M.cfg.sesskey;
      var ajax = $.ajax({
        method: "POST",
        url: "ajax.php",
        data: data
      })
        .done(function (ret) {
          //console.log(data.action, 'ret', ret);
        }).fail(function (ret) {
          var errorMsg = '';
          if (ret.responseText[0] == '<') {
            // html
            errorMsg = $(ret.responseText).find('.errormessage').text();
          }
          console.log("Error in action '" + data.action + "'", errorMsg, 'ret', ret);
        });

      return ajax;
    },

    popup_iframe: function (config) {
      // allow passing of an url
      if (typeof config == 'string') {
        config = {
          url: config
        };
      }

      var popupInit = function (config, blockContent) {
        var popup = /*this.last_popup =*/ new M.core.dialogue({
          headerContent: config.headerContent || config.title || 'Popup', // M.str.moodle.loadinghelp, // previousimagelink + '<div id=\"imagenumber\" class=\"imagetitle\"><h1> Image '
          // + screennumber + ' / ' + this.imageidnumbers[imageid] + ' </h1></div>' + nextimagelink,
          bodyContent: blockContent,
          visible: true, //by default it is not displayed
          modal: false, // sollte true sein, aber wegen moodle bug springt dann das fenster immer nach oben
          zIndex: 1000,
          // ok: width: '80%',
          // ok: width: '500px',
          // ok: width: null, = automatic
          height: config.height || '80%',
          width: config.width || '85%',
        });

        // disable scrollbars
        $(window).disablescroll();

        // hack my own overlay, because moodle dialogue modal is not working
        var overlay = $('<div style="opacity:0.7; filter: alpha(opacity=20); background-color:#000; width:100%; height:100%; z-index:10; top:0; left:0; position:fixed;"></div>')
          .appendTo('body');
        // hide popup when clicking overlay
        overlay.click(function () {
          popup.hide();
        });

        var orig_hide = popup.hide;
        popup.hide = function () {

          if (config.onhide) {
            config.onhide();
          }

          // remove overlay, when hiding popup
          overlay.remove();

          // enable scrolling
          $(window).disablescroll('undo');

          // call original popup.hide()
          orig_hide.call(popup);
        };

        popup.remove = function () {
          if (this.$body.is(':visible')) {
            this.hide();
          }

          this.destroy();
        };
        return popup;
      }

      // preload M.core.dialogue
      Y.use('moodle-core-notification-dialogue', function () {
        if (config.fromAjax) {
          if ($(block_exacomp.last_popup).length) {
            block_exacomp.last_popup.hide();
          }
          var canvasid = 'exacomp_spinner' + Math.floor(Date.now() / 1000);
          // add spinner
          var blockContent = '<div width="100%" height="100%" style="width: 100%; height:100%; display: table;">' +
            '<div style="text-align: center; display: table-cell; vertical-align: middle;">' +
            '<canvas id="' + canvasid + '" height="120" width="120" style="background: transparent;" />' +
            '</div>' +
            '</div>';
          block_exacomp.last_popup = popupInit(config, blockContent);
          var canvas = document.getElementById(canvasid);
          var context = canvas.getContext('2d');
          var start = new Date();
          var lines = 16,
            cW = context.canvas.width,
            cH = context.canvas.height;

          var draw = function () {
            var rotation = parseInt(((new Date() - start) / 1000) * lines) / lines;
            context.save();
            context.clearRect(0, 0, cW, cH);
            context.translate(cW / 2, cH / 2);
            context.rotate(Math.PI * 2 * rotation);
            for (var i = 0; i < lines; i++) {

              context.beginPath();
              context.rotate(Math.PI * 2 / lines);
              context.moveTo(cW / 10, 0);
              context.lineTo(cW / 4, 0);
              context.lineWidth = cW / 30;
              context.strokeStyle = "rgba(150,150,150," + i / lines + ")";
              context.stroke();
            }
            context.restore();
          };
          window.setInterval(draw, 1000 / 30);

          var ajaxRequest = '';
          block_exacomp.call_ajax(Object.assign({
              action: 'grade_example_related_form'
            },
            config.exaData
          )).then(function (msg) {
            ajaxRequest = msg;
            block_exacomp.last_popup.hide();
            canvas.remove();
            var blockContent = '<div width="100%" height="100%" style="max-height:100%; overflow: scroll;">' + ajaxRequest + '</div>';
            block_exacomp.last_popup = popupInit(config, blockContent);
          });
        } else {
          var blockContent = '<iframe src="' + config.url + '" width="100%" height="100%" frameborder="0"></iframe>';
          block_exacomp.last_popup = popupInit(config, blockContent);
          /*var popup = this.last_popup = new M.core.dialogue({
              headerContent: config.headerContent || config.title || 'Popup', // M.str.moodle.loadinghelp, // previousimagelink + '<div id=\"imagenumber\" class=\"imagetitle\"><h1> Image '
              // + screennumber + ' / ' + this.imageidnumbers[imageid] + ' </h1></div>' + nextimagelink,
              bodyContent: blockContent,
              visible: true, //by default it is not displayed
              modal: false, // sollte true sein, aber wegen moodle bug springt dann das fenster immer nach oben
              zIndex: 1000,
              // ok: width: '80%',
              // ok: width: '500px',
              // ok: width: null, = automatic
              height: config.height || '80%',
              width: config.width || '85%',
          });*/
        }


      });

      // TODO: return popup as a promise?
    },

    popup_close: function () {
      var parent = window.opener || window.parent;

      // close inline popup
      if (parent.block_exacomp.last_popup) {
        parent.block_exacomp.last_popup.hide();
      } else {
        // OR close real window
        window.close();
      }
    },

    popup_close_and_notify: function (func, args) {
      var parent = window.opener || window.parent;

      // first notify parent
      parent.block_exacomp[func].apply(parent.block_exacomp, args);

      // then close popup and unload iframe etc.
      this.popup_close();
    },

    popup_close_and_forward: function (url) {
      var parent = (window.opener || window.parent);

      this.popup_close();
      parent.location.href = url;
    },

    popup_close_and_reload: function () {
      var parent = window.opener || window.parent;

      this.popup_close();
      if (parent.block_exacomp && parent.block_exacomp.reload_action) {
        parent.block_exacomp.reload_action();
      } else {
        parent.location.reload(true);
      }
    },

    column_selector: function (tablesearch, options) {

      options = options || {};
      options = $.extend({
        title_colspan: 1,
        item_colspan: 1,
      }, options);

      $(function () {
        var $table = $(tablesearch);

        var table_total_colspan = 0;
        $table.find('tr:first').find('td,th').each(function () {
          table_total_colspan += this.colSpan;
        });

        var item_count = (table_total_colspan - options.title_colspan) / options.item_colspan;
        var items_per_page = 25;
        var $content = $('#col_selector_content');

        if (item_count <= items_per_page) {
          $table.show();
          return;
        }

        function select_group() {
          $('.colgroup-button').css('font-weight', 'normal');
          $(this).css('font-weight', 'bold');

          var groupid = $(this).attr('exa-groupid');

          $table.find('tr').each(function () {
            var rowColSpan = 0;
            $(this).find('th,tr').each(function (i, col) {
              if (rowColSpan < options.title_colspan) {
                // nothing, always show title
              } else if (groupid === 'all') {
                $(col).show();
              } else if (Math.floor((rowColSpan - options.title_colspan) / items_per_page) == groupid) {
                $(col).show();
              } else {
                $(col).hide();
              }

              rowColSpan += this.colSpan;
            });
          });

          /*$table.find('tr').each(function(){
            var rowColSpan = 0;
            $(this).find('td,tr').each(function(i, col){
              if (rowColSpan < options.title_colspan) {
                // nothing, always show title
              } else if (groupid === 'all') {
                $(col).show();
              } else if (Math.floor((rowColSpan - options.title_colspan) / items_per_page) == groupid) {
                $(col).show();
              } else {
                $(col).hide();
              }

              rowColSpan += this.colSpan;
            });
          });*/


          return false;
        }

        $content.append('<b>' + M.util.get_string('columnselect', 'block_exacomp') + ':</b>');
        for (var i = 0; i < Math.ceil(item_count / items_per_page); i++) {
          $content.append(' ');
          $('<a href="#" class="colgroup-button" exa-groupid="' + i + '">'
            + (i * items_per_page + 1) + '-' + Math.min(item_count, (i + 1) * items_per_page) + '</a>')
            .click(select_group)
            .appendTo($content);
        }

        $content.append(' ');
        $('<a href="#" class="colgroup-button colgroup-button-all" exa-groupid="all">'
          + M.util.get_string('all', 'moodle') + '</a>')
          .click(select_group)
          .appendTo($content);

        $content.find('a:first').click();
        $table.show();
      });

      document.write('<div id="col_selector_content"></div>');
    }
  };

  $(function () {
    // handle: de-du, de, en, en-us,... and strip -du, ...
    var lang = $('html').prop('lang').replace(/\-.*/, '');

    if ($.datepicker) {
      $.datepicker.setDefaults({
        dateFormat: 'yy-mm-dd'
      });

      if (lang == 'de') {
        $.datepicker.setDefaults({
          showOn: "both",
          buttonImageOnly: true,
          buttonImage: "pix/calendar_alt_stroke_12x12.png",
          buttonText: "Calendar",
          prevText: '&#x3c;zurück',
          prevStatus: '',
          prevJumpText: '&#x3c;&#x3c;',
          prevJumpStatus: '',
          nextText: 'Vor&#x3e;',
          nextStatus: '',
          nextJumpText: '&#x3e;&#x3e;',
          nextJumpStatus: '',
          currentText: 'heute',
          currentStatus: '',
          todayText: 'heute',
          todayStatus: '',
          clearText: '-',
          clearStatus: '',
          closeText: 'schließen',
          closeStatus: '',
          monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
            'Juli', 'August', 'September', 'Oktober', 'November',
            'Dezember'],
          monthNamesShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
            'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
          dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch',
            'Donnerstag', 'Freitag', 'Samstag'],
          dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
          dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
          showMonthAfterYear: false,
          showOn: 'both'
        });
      }
      $(".datepicker").datepicker();

      // set minDate to today for datepicker-mindate class
      $(".datepicker.datepicker-mindate").datepicker("option", "minDate", 0);
    }

    if ($().tooltip) {
      // only if we have the tooltip function
      $('.exabis-tooltip').tooltip({
        items: '[title], [data-tooltip-content]',
        // retreave content as html
        content: function () {
          console.log($(this).data('tooltip-content'));
          return $(this).data('tooltip-content') || $(this).prop('title');
        },

        // copy from http://stackoverflow.com/a/16663648/5164463
        // disable closing of tooltip if you hover over it!
        show: null, // show immediately
        open: function (event, ui) {
          if (typeof (event.originalEvent) === 'undefined') {
            return false;
          }

          var $id = $(ui.tooltip).attr('id');

          // close any lingering tooltips
          $('div.ui-tooltip').not('#' + $id).remove();
        },
        close: function (event, ui) {
          ui.tooltip.hover(function () {
              $(this).stop(true).fadeTo(400, 1);
            },
            function () {
              $(this).fadeOut('400', function () {
                $(this).remove();
              });
            });
        }
      });
    }

    // student selector
    $('select[name=exacomp_competence_grid_select_student]').change(function () {
      displaySpinner();
      $E.set_location_params({studentid: this.value});
    });

    // convert exa-tree to rg2
    $('ul.exa-tree').each(function () {
      var $tree = $(this);
      var $table = $('<table class="exabis_comp_comp rg2 ' + $tree.attr('class').replace(/exa\-tree/g, 'rg2') + '"></table>');
      $table.insertAfter(this).wrap('<div class="exabis_competencies_lis"></div>');

      $tree.find('li').each(function () {
        var level = $(this).parentsUntil($tree, 'ul').length;
        $('<tr class="' + $(this).attr('class') + ' rg2-level-' + level + '"><td class="rg2-arrow rg2-indent"><div></div></td></tr>')
          .appendTo($table).find('div').append($(this).clone().find('>ul').remove().end().contents());
      });

      $table.trigger('rg2.init');
      $table.find('tr.rg2-header').addClass('highlight');

      $tree.remove();
    });

    // show tree menus
    /*
    var trees = $('ul.exa-tree');
    // ddtree needs those, why didn't they do it with css?
    ddtreemenu.closefolder = M.cfg.wwwroot+"/blocks/exacomp/javascript/simpletreemenu/closed.gif";
    ddtreemenu.openfolder = M.cfg.wwwroot+"/blocks/exacomp/javascript/simpletreemenu/open.gif";

    // mark open elements, ddtreemenu opens them automatically
    trees.filter('.exa-tree-reopen-checked').find('ul').has("input[type=checkbox]:checked").attr('rel', 'open');

    // init the trees
    trees.addClass('treeview').each(function(i){
      var id = 'simple-tree-'+i;
      $(this).attr('id', id);
      ddtreemenu.createTree(id, false);
    });
    trees.show();

    // prevent item from open/close when clicking checkbox
    trees.find('input').click(function(e){
      e.stopPropagation();
    });

    // open all
    trees.filter('.exa-tree-open-all').each(function(){
      ddtreemenu.flatten($(this).attr('id'), 'expand');
    });
    /* */
  });

// collapsible
  $(document).on('click', '.exa-collapsible > legend', function () {
    $(this).parent().toggleClass('exa-collapsible-open');
  });


// rowgroup2
  (function () {

    var options = {
      check_uncheck_parents_children: false,
      reopen_checked: false,
    };
    window.exabis_rg2 = {
      options: options,
      get_row: get_row,
      get_children: get_children,
      get_parents: get_parents,
    };

    var id_i = 0;

    function get_row(dom) {
      var $tr = $(dom).closest('tr.rg2');
      $tr.level = get_level($tr) || 0;
      if ($tr.attr('exa-rg2-id')) {
        $tr.id = $tr.attr('exa-rg2-id');
      } else {
        id_i++;
        $tr.id = 'no-id-' + id_i;
        $tr.attr('exa-rg2-id', $tr.id);
      }
      return $tr;
    }

    function get_level($tr) {
      $tr = $($tr);
      if (!$tr.attr('class')) return null;
      var matches = $tr.attr('class').match(/(^|\s)rg2-level-([0-9]+)(\s|$)/);
      return matches ? parseInt(matches[2]) : null;
    }

    function get_parents(item) {
      var $row = get_row(item),
        level = $row.level - 1,
        parents = [];
      $row.prevAll('tr.rg2').each(function () {
        if (get_row(this).level == level) {
          parents[level] = this;
          level--;
        }
      });
      return $(parents);
    }

    function get_children(item, deep) {
      var $row = get_row(item);
      var children = [];
      $row.nextAll('tr.rg2').each(function () {
        var $child_row = get_row(this);
        if ($child_row.level > $row.level + 1) {
          if (deep) {
            children.push(this);
          }
        } else if ($child_row.level == $row.level + 1) {
          children.push(this);
        } else {
          return false;
        }
      });
      return $(children);
    }

    function get_table_storageid($table) {
      return 'rg2-id-storage-' + ($table.attr('exa-rg2-storageid') || document.location.pathname);
    }

    function update_table($table) {
      var visible_level = 0;

      $table.find('tr.rg2').each(function () {
        var $tr = $(this),
          level = get_level($tr);

        if (level === null) {
          visible_level = 0;
          return;
        }

        if (level + 1 === get_level($tr.next())) {
          // is header
          $tr.addClass('rg2-header');
          if (visible_level >= level) {
            if ($tr.is('.open')) {
              visible_level = level + 1;
            } else {
              visible_level = level;
            }
          }
        }

        if (visible_level >= level) {
          $tr.show();
        } else {
          $tr.hide();
        }
      });
    }

    function get_tables() {
      return $('table.rg2');
    }

    function get_table(dom) {
      return $(dom).closest('table.rg2');
    }

    $(document).on('click', '.rg2-header .rg2-arrow', function (event) {
      if (event.isDefaultPrevented() || $(event.target).is('input, select') || $(this).closest('.rg2-locked').length) {
        // the click handler on an edit button is called, so don't open/close menu
        return;
      }

      $(this).closest('.rg2-header').toggleClass('open');
      update_table(get_table(this));
    });
    // stop labels in headers to check/uncheck. we just want open/close here!
    $(document).on('click', '.rg2-header label', function (event) {
      event.preventDefault();

      $(this).closest('.rg2-header').toggleClass('open');
      update_table(get_table(this));
    });

    $(window).on('beforeunload', function () {
      // save state before unload
      get_tables().each(function () {
        var ids = $(this).find('.rg2.open:not(.rg2-locked)').map(function () {
          return get_row(this).id;
        }).toArray();
        localStorage.setObject(get_table_storageid($(this)), ids);
      });
    });

    $(document).on('click', '.rg2 .selectallornone', function () {
      $(this).trigger('rg2.open');

      var $children = get_children(this);
      $children.find(':checkbox').prop('checked', $children.find(':checkbox:not(:checked)').length > 0);
    });

    $(document).ready(function () {
      $('.selectallornone').on('click', function () {
        var val = $("input:checkbox").get(1).checked;
        $("input:checkbox").each(function () {
          if ($(this).attr('name').substring(0, 4) == 'data') {
            if (val) {
              $(this).prop('checked', false);
            } else {
              $(this).prop('checked', true);
            }
          }
        });
      });
    });

    $(document).on('click', '.schooltype', function(event){
      event.preventDefault();
      var i = $(this).children().get(0);
      var hidden = $(this).next().get(0);
      console.log(hidden);
      if(i.classList.contains('fa-eye')){
        i.classList.remove('fa-eye');
        i.classList.add('fa-eye-slash');
        hidden.value = 1;
      } else {
        i.classList.add('fa-eye');
        i.classList.remove('fa-eye-slash');
        hidden.value = 0;
      }

    });

    // init
    $(document).on('rg2.init', 'table.rg2', function () {
      var $table = $(this);

      // add class to rows
      $table.find('> tr, > tbody > tr').addClass('rg2');

      if ($table.hasClass('rg2-opened-first')) {
        $table.find('tr.rg2').each(function () {
          $(this).addClass('open');
        });
        update_table($table);
      }

      $table.on('rg2.update', function () {
        update_table($table);
      });
      $table.on('rg2.open', 'tr.rg2', function () {
        $(this).addClass('open');
        update_table($table);
      });
      $table.on('rg2.close', 'tr.rg2', function () {
        $(this).removeClass('open');
        update_table($table);
      });
      $table.on('rg2.open-parents', 'tr.rg2', function () {
        get_parents(this).addClass('open');
        update_table($table);
      });
      $table.on('rg2.lock', 'tr.rg2', function () {
        $(this).addClass('rg2-locked');
        $(this).removeClass('open');
        $(this).find('.rg2-arrow-disabled').addClass('rg2-arrow').removeClass('rg2-arrow-disabled');

        update_table($table);
      });
      $table.on('rg2.unlock', 'tr.rg2', function () {
        $(this).removeClass('rg2-locked');

        update_table($table);
      });

      $table.find('.rg2-level-0').show();

      if (options.check_uncheck_parents_children || $table.is('.rg2-check_uncheck_parents_children')) {
        $table.find(':checkbox').click(function () {
          get_children(this, true).find(":checkbox").prop('checked', $(this).prop('checked'));
          if (!$(this).prop('checked')) {
            // parents, only for uncheck
            get_parents(this).find(":checkbox").prop('checked', false);
          }
        });
      }

      // reopen saved states
      var ids = localStorage.getObject(get_table_storageid($table));
      if (ids) {
        $.each(ids, function (tmp, id) {
          // only open if not locked
          $table.find('.rg2:not(.rg2-locked)[exa-rg2-id="' + id + '"]').addClass('open');
        });
      }

      // reopen checked
      if (options.reopen_checked || $table.is('.rg2-reopen-checked')) {
        $table.find(':checkbox:checked').trigger('rg2.open-parents');
      }

      // open all
      if ($table.is('.rg2-open-all')) {
        $table.find('.rg2-header').addClass('open');
      }

      // close locked
      $table.find('tr.rg2-locked').removeClass('open');

      update_table($table);

      // if just one item, always open and hide arrow
      if ($table.find('.rg2-level-0.rg2-header:not(.rg2-locked)').length == 1) {
        $table.find('.rg2-level-0.rg2-header:not(.rg2-locked)').addClass('open').find('.rg2-arrow').removeClass('rg2-arrow').addClass('rg2-arrow-disabled');
        if ($table.find('.rg2-level-1.rg2-header:not(.rg2-locked)').length == 1) {
          $table.find('.rg2-level-1.rg2-header:not(.rg2-locked)').addClass('open').find('.rg2-arrow').removeClass('rg2-arrow').addClass('rg2-arrow-disabled');
        }
        update_table($table);
      }
    });

    $(function () {
      // add class to tables
      // $('tr.rg2, table.rg2, .rg2-level-0').closest('table').addClass('rg2');

      get_tables().trigger('rg2.init');
    });
  })();

  $(function () {
    function highlight_cells(date1, date2) {
      // Reset the gradings within the given range
      $("td[exa-timestamp]").each(function (index) {
        $(this).removeClass('highlight_cell');

        tr = $(this).closest('tr');
        if (tr.hasClass('highlight_cell')) {
          tr.removeClass('highlight_cell');
        }
      });

      var date1_s = Math.round(new Date(date1).getTime() / 1000);
      // always highlight until the end of the selected date
      var date2_s = new Date(date2);
      date2_s.setDate(date2_s.getDate() + 1);
      date2_s = Math.round(date2_s.getTime() / 1000);

      // Highlight the gradings within the given range
      $("td[exa-timestamp]").each(function (index) {
        if ($(this).attr("exa-timestamp") >= date1_s && $(this).attr("exa-timestamp") < date2_s) {
          $(this).addClass('highlight_cell');

          tr = $(this).closest('tr');
          if (tr.hasClass('comparison_topic') || tr.hasClass('comparison_desc') || tr.hasClass('comparison_mat')) {
            tr.addClass('highlight_cell');
          }
        }
      });
    }

    function update_statistic_tables(date1, date2) {
      var date1_s = (new Date(date1).getTime() / 1000);
      // always highlight until the end of the selected date
      var date2_s = new Date(date2);
      date2_s.setDate(date2_s.getDate() + 1);
      date2_s = Math.round(date2_s.getTime() / 1000);

      // make ajax call for new data / html code

      $('div[class="statistictables"]').each(function () {
        var subjectid = $(this).attr('exa-subjectid');
        var courseid = $(this).attr('exa-courseid');
        block_exacomp.call_ajax({
          studentid: block_exacomp.get_studentid(),
          subjectid: subjectid,
          courseid: courseid,
          start: date1_s,
          end: date2_s,
          action: 'get_statistics_for_profile'
        }).done(function (html) {
          $('div[class="statistictables"][exa-subjectid="' + subjectid + '"]').replaceWith(html);
        });
      });
    }

    if (is_page('group_reports')) {
      var $picker = $('<input id="daterangepicker" />');
      var allowClearSelect = true;

      $picker.insertAfter('.range-inputs');
      $picker.dateRangePicker({
        separator: ' ' + M.util.get_string('seperatordaterange', 'block_exacomp') + ' ',
        format: 'DD.MMM.YYYY',
        startOfWeek: 'monday',
        showShortcuts: true,
        shortcuts: {
          'prev-days': [3, 5, 7],
          'prev': ['week', 'month', 'year'],
          'next-days': null,
          'next': null
        }
      }).bind('datepicker-change', function (event, obj) {
        $('.range-inputs input[name*="from"]').val(obj.date1.getTime() / 1000 | 0);
        $('.range-inputs input[name*="to"]').val(obj.date2.getTime() / 1000 | 0);

        if (allowClearSelect) {
          $('select[name="daterangeperiods"]').val('');
        }
      });

      if ($('.range-inputs input[name*="from"]').val() && $('.range-inputs input[name*="to"]').val()) {
        $picker.data('dateRangePicker').setDateRange(new Date($('.range-inputs input[name*="from"]').val() * 1000), new Date($('.range-inputs input[name*="to"]').val() * 1000));
      }

      // perioden auswahl
      $('select[name="daterangeperiods"]').change(function (event) {
        if (!$(this).val()) {
          $('#clear-range').click();
        } else {
          var fromTo = $(this).val().split('-');
          allowClearSelect = false;
          $('input[id="daterangepicker"]').data('dateRangePicker').setDateRange(new Date(fromTo[0] * 1000), new Date(fromTo[1] * 1000));
          allowClearSelect = true;
        }
      });

      $('#clear-range').click(function (event) {
        $('input[id="daterangepicker"]').data('dateRangePicker').clear();
        $('select[name="daterangeperiods"]').val('');
        $('.range-inputs input').val('');
      });
    } else if ($('input[id="daterangepicker"]').length) {
      var allowClearSelect = true;
      $('input[id="daterangepicker"]').dateRangePicker({
        separator: ' ' + M.util.get_string('seperatordaterange', 'block_exacomp') + ' ',
        format: 'DD.MMM.YYYY',
        startOfWeek: 'monday',
        showShortcuts: true,
        shortcuts: {
          'prev-days': [3, 5, 7],
          'prev': ['week', 'month', 'year'],
          'next-days': null,
          'next': null
        }
      }).bind('datepicker-change', function (event, obj) {
        sessionStorage.setItem('date1', obj.date1);
        sessionStorage.setItem('date2', obj.date2);
        sessionStorage.setItem('value', obj.value);

        update_statistic_tables(obj.date1, obj.date2);
        highlight_cells(obj.date1, obj.date2);
        if (allowClearSelect) {
          $('select[name="daterangeperiods"]').val('');
        }
      });

      if (sessionStorage.getItem('date1') != null && sessionStorage.getItem('date2') != null) {
        $('input[id="daterangepicker"]').data('dateRangePicker').setDateRange(new Date(sessionStorage.getItem('date1')), new Date(sessionStorage.getItem('date2')));
        highlight_cells(sessionStorage.getItem('date1'), sessionStorage.getItem('date2'));
      }

      // perioden auswahl
      $('select[name="daterangeperiods"]').change(function (event) {
        var fromTo = $(this).val().split('-');
        allowClearSelect = false;
        $('input[id="daterangepicker"]').data('dateRangePicker').setDateRange(new Date(fromTo[0] * 1000), new Date(fromTo[1] * 1000));
        allowClearSelect = true;
      });

      $('#clear-range').click(function (event) {
        event.preventDefault();

        $('input[id="daterangepicker"]').data('dateRangePicker').clear();
        $('select[name="daterangeperiods"]').val('');

        sessionStorage.removeItem('date1');
        sessionStorage.removeItem('date2');
        sessionStorage.removeItem('value');

        // Reset the gradings within the given range
        $("td[exa-timestamp]").each(function (index) {
          $(this).removeClass('highlight_cell');

          tr = $(this).closest('tr');
          if (tr.hasClass('highlight_cell')) {
            tr.removeClass('highlight_cell');
          }
        });

        update_statistic_tables(0, 0);
      });

      $(document).on('change', '[name^=datadescriptors\-], [name^=niveau_descriptor\-], [name^=dataexamples\-], [name^=niveau_examples\-], [name^=add-grading\-], [name^=niveau_topic\-], [name^=datatopics\-], [name^=niveau_subject\-], [name^=datasubjects\-], [name^=niveau_crosssub\-], [name^=datacrosssubs\-]', function () {
        // one example can have several inputs/selects (if it is attached to several descriptors), so iterate over all
        if ($(this).attr("name").indexOf("example") !== -1) {
          $("[name=" + $(this).attr("name") + "]").each(function () {
            if (Date.now() >= new Date(sessionStorage.getItem('date1')) && Date.now() <= new Date(sessionStorage.getItem('date2')))
              $(this).closest('td').addClass('highlight_cell');

            $(this).closest('td').attr('exa-timestamp', Math.floor(Date.now() / 1000));
          });
        } else {
          if (Date.now() >= new Date(sessionStorage.getItem('date1')) && Date.now() <= new Date(sessionStorage.getItem('date2')))
            $(this).closest('td').addClass('highlight_cell');

          $(this).closest('td').attr('exa-timestamp', Math.floor(Date.now() / 1000));
        }
      });
    }
  });

  // import: list of grids: select/deselect
  $(document).on('click', '.exacomp_import_select_sublist', function (e) {
    e.preventDefault();
    var index = $(this).attr('data-targetList');
    var result = $(this).attr('data-selected');
    if (index == -1) {
      var checkboxes = $('ul.exacomp_import_grids_list').find('input:checkbox');
    } else {
      var checkboxes = $('ul.exacomp_import_grids_list[data-pathIndex=' + index + ']').find('input:checkbox');
    }
    if (result == 1)
      checkboxes.prop('checked', true);
    else
      checkboxes.prop('checked', false);
  });

  // import: click on 'All subjects'
  function importAllSubjectsClicked(element) {
    if ($(element).is(':checked')) {
      $('div#import-subjects-list .fitem_fcheckbox').each(function () {
        $(this).find('input').attr('disabled', true);
        $('#import-subjects-list h4').addClass('text-muted');
        $('#import-subjects-list a.exacomp_import_select_sublist').closest('small').hide();
        $(this).find('label').addClass('text-muted');
      });
    } else {
      $('div#import-subjects-list .fitem_fcheckbox').each(function () {
        $(this).find('input').attr('disabled', false);
        $('#import-subjects-list h4').removeClass('text-muted');
        $('#import-subjects-list a.exacomp_import_select_sublist').closest('small').show();
        $(this).find('label').removeClass('text-muted');
      });
    }
  };

  $(document).on('change', 'input.import-all-subjects', function (e) {
    importAllSubjectsClicked($(this));
  });
  $(function () {
    importAllSubjectsClicked($('input.import-all-subjects'));
  });

  // add new taxonomy button
  $(document).on('click', '#exacomp_add_taxonomy_button', function (e) {
    e.preventDefault();
    if ($('#exacomp-table-taxonomies').length) {
      $('#exacomp-table-taxonomies > tbody:last-child').append('<tr><td class="cell"><input type="text" name="datanew[]" value="" placeholder="" class="form-control " /></td><td class="cell" colspan="10">&nbsp;</td></tr>');
    }
  });

  // collapsible content
  $(document).on('click', '.exacomp-collapse-toggler', function () {
    var target = $(this).attr('data-target');
    var state = $(this).attr('data-expanded');
    if (state == 1) {
      $(this).attr('data-expanded', 0);
      $('#' + target).hide();
      $(this).find('.collapsed_icon').show();
      $(this).find('.expanded_icon').hide();
    } else {
      $(this).attr('data-expanded', 1);
      $('#' + target).show();
      $(this).find('.collapsed_icon').hide();
      $(this).find('.expanded_icon').show();
    }
  })

  // message about unsaved changes in the forms
  // make a new class for needed forms: "checksaving_on_leavepage"
  $(document).on('change', "form.checksaving_on_leavepage :input", function () {
    formunsaved = true;
  });
  $(document).on('click', 'form.checksaving_on_leavepage input[type="submit"]', function () {
    formunsaved = false; // do not shown the message if save button is pressed
  });
  window.onbeforeunload = function unloadPage() {
    if (formunsaved && $('form.checksaving_on_leavepage').length) {
      // show message, but this message often is reloading via default browser message
      // Looks like almost all browsers closed possibility to change this message.
      return M.str.block_exacomp.donotleave_page_message + '  ';
    }
  };

  window.displaySpinner = function () {
    var canvasid = 'exacomp_spinner' + Math.floor(Date.now() / 1000);
    var blockContent = '<div width="100%" height="100%" style="width: 100%; height:100%; display: table; position: fixed; top: 0px; left: 0px;">' +
      '<div style="text-align: center; display: table-cell; vertical-align: middle;">' +
      '<canvas id="' + canvasid + '" height="120" width="120" style="background: transparent;" />' +
      '</div>' +
      '</div>';
    $('body').append(blockContent);
    var canvas = document.getElementById(canvasid);
    var context = canvas.getContext('2d');
    var start = new Date();
    var lines = 16,
      cW = context.canvas.width,
      cH = context.canvas.height;

    var draw = function () {
      var rotation = parseInt(((new Date() - start) / 1000) * lines) / lines;
      context.save();
      context.clearRect(0, 0, cW, cH);
      context.translate(cW / 2, cH / 2);
      context.rotate(Math.PI * 2 * rotation);
      for (var i = 0; i < lines; i++) {

        context.beginPath();
        context.rotate(Math.PI * 2 / lines);
        context.moveTo(cW / 10, 0);
        context.lineTo(cW / 4, 0);
        context.lineWidth = cW / 30;
        context.strokeStyle = "rgba(150,150,150," + i / lines + ")";
        context.stroke();
      }
      context.restore();
    };
    window.setInterval(draw, 1000 / 30);
    return canvasid;
  };

}();

$(function () {
  $('.move-into-sibling-link').each(function () {
    // some hack of Moodle templating - insert element into tab-link element
    var targetLink = $(this).siblings('.nav-link');
    targetLink.append("&nbsp;"); // &nbsp;
    targetLink.append($(this));
    $(this).on('click', function () {
      return false;
    });
  });
});
