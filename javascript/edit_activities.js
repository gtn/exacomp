// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

exabis_rg2.options.reopen_checked = true;

$(function(){
    $('.exabis_comp_comp').on('click', 'button.activity-export-btn', function(e) {
        e.preventDefault();
        var activityid = $(this).val();
        /*block_exacomp.call_ajax({
            activityid : activityid,
            action : 'export-activity'
        });*/
        var urlparams = 'action=export-activity&activityid='+activityid+'&courseid='+$E.get_param('courseid')+'&sesskey='+M.cfg.sesskey;
        var request = new XMLHttpRequest();
        request.open('POST', 'ajax.php', true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.responseType = 'blob';
        request.onload = function() {
            // Only if all ok: status code 200
            if(request.status === 200) {
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