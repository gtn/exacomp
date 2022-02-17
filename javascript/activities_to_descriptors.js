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

$(function () {
  $('.exabis_comp_comp').on('change', 'input.topiccheckbox', function (e) {
    var topicId = $(this).attr('data-topicId');
    var activityId = $(this).attr('data-activityId');
    $(this).toggleClass('checked-topic');
    var descriptors = $('.exabis_comp_comp').find('.descriptorcheckbox[data-topicId="' + topicId + '"][data-activityId="' + activityId + '"]');
    console.log(descriptors);
    if ($(this).hasClass('checked-topic')) {
      descriptors.prop('checked', true);
    } else {
      descriptors.prop('checked', false);
    }
  });

  $('.exabis_comp_comp').on('click', 'button.activity-export-btn', function (e) {
    e.preventDefault();
    var activityid = $(this).val();
    /*block_exacomp.call_ajax({
        activityid : activityid,
        action : 'export-activity'
    });*/
    var urlparams = 'action=export-activity&activityid=' + activityid + '&courseid=' + $E.get_param('courseid') + '&sesskey=' + M.cfg.sesskey;
    var request = new XMLHttpRequest();
    request.open('POST', 'ajax.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.responseType = 'blob';
    request.onload = function () {
      // Only if all ok: status code 200
      if (request.status === 200) {
        // Try to find out the filename from the content disposition `filename` value
        var disposition = request.getResponseHeader('content-disposition');
        var matches = /"([^"]*)"/.exec(disposition);
        var filename = (matches != null && matches[1] ? matches[1] : 'exacomp.zip');
        // downloading: simulate link clicking
        var blob = new Blob(
          [request.response],
          {type: 'application/zip'}
        );
        var tempLink = document.createElement('a');
        tempLink.href = window.URL.createObjectURL(blob);
        tempLink.download = filename;
        document.body.appendChild(tempLink);
        tempLink.click();
        document.body.removeChild(tempLink);
      }
    };
    request.send(urlparams);
  });
});
