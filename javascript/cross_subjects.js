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

if (block_exacomp.get_param('action') == 'share') {
  $(document).on('click', 'form#share input[name=share_all]', function () {
    // disable if checked
    $("input[name='studentids[]']").attr('disabled', this.checked);
  });
}

if (block_exacomp.get_param('action') == 'descriptor_selector') {
  $(function () {
    // disable subitems, which also prevents them from getting submitted
    $('table.rg2 :checkbox').click(function () {
      exabis_rg2.get_children(this, true).find(':checkbox')
        .prop('disabled', $(this).is(':checked'))
        .prop('checked', $(this).is(':checked'));
    });
    $('table.rg2 :checkbox:checked').triggerHandler('click');
  });
}
