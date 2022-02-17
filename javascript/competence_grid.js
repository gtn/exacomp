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

  $(document).on('click', '.switchtextalign', function () {
    if ($("span[class='rotated-text']").length > 0) {
      $("span[class='rotated-text']").attr("class", "rotated-text-disabled");
      $("span[class='rotated-text__inner']").attr("class", "rotated-text__inner_disabled");
    } else {
      $("span[class='rotated-text-disabled']").attr("class", "rotated-text");
      $("span[class='rotated-text__inner_disabled']").attr("class", "rotated-text__inner");
    }
  });

  // student selector
  $(function () {
    $('select[name=exacomp_competence_grid_report]').change(function () {
      block_exacomp.set_location_params({report: this.value});
    });
  });
})(jQueryExacomp);
