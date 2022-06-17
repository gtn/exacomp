<?php
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

namespace qbank_questiontodescriptor;

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question creator.
 *
 * @package   qbank_viewcreator
 * @copyright 2009 Tim Hunt
 * @author    2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class descriptor_link_column extends column_base {

    public function get_name(): string {
        return 'questiontodescriptor';
    }

    public function get_title(): string {
        return "Link";

    }

    protected function display_content($question, $rowclasses): void {
        global $USER, $DB;
        echo '<a href="#" class=" btn btn-primary btn-sm competences' . $question->id . '" role="button"> ' . block_exacomp_get_string("questlink") . ' </a>';


        $cache = \cache::make('block_exacomp', 'visibility_cache');
        $comptree = $cache->get('comptree');


        ?>
        <div style="display: none">
            <div id="inline_comp_tree" class="ict<?php echo $question->id; ?>" style='padding: 10px; background: #fff;'>
                <h4>
                    <?php echo block_exacomp_get_string("opencomps") ?>
                </h4>

                <a href="javascript:ddtreemenu.flatten('comptree<?php echo $question->id; ?>', 'expand')"><?php echo block_exacomp_get_string("expandcomps") ?>
                </a> | <a href="javascript:ddtreemenu.flatten('comptree<?php echo $question->id; ?>', 'contact')"><?php echo block_exacomp_get_string("contactcomps") ?>
                </a>

                <?php echo block_exacomp_fill_comp_tree($question, $comptree); ?>
            </div>
        </div>

        <script type="text/javascript">
            //<![CDATA[
            jQueryExacomp(function ($) {

                $("#treeform<?php echo $question->id; ?> :checkbox").click(function (e) {
                    // Prevent item open/close.
                    e.stopPropagation();
                });

                var $compids = $("input[name=compids]");
                var $descriptors = $("#treeform<?php echo $question->id; ?> :checkbox");

                $(".competences<?php echo $question->id; ?>").colorbox({
                    width: "75%", height: "75%", inline: true, href: ".ict<?php echo $question->id; ?>", onClosed: function () {
                        // Save ids to input field.
                        var compids = "";
                        $descriptors.filter(":checked").each(function () {
                            compids += this.value + ", ";
                        });
                        $compids.val(compids);

                        build_competence_output();
                    }
                });
                ddtreemenu.createTree("comptree<?php echo $question->id; ?>", true);

                function build_competence_output() {
                    var $tree = $("#comptree<?php echo $question->id; ?>").clone();
                    // Remove original id, conflicts with real tree.
                    $tree.attr("id", "comptree<?php echo $question->id; ?>-selected");

                    // Delete all not checked.
                    $tree.find("li").each(function () {
                        if (!$(this).find(":checked").length) {
                            $(this).remove();
                        }
                    });

                    // Delete checkboxes.
                    $tree.find(":checkbox").remove();

                    $("#comptitles").empty().append($tree);
                    ddtreemenu.createTree("comptree<?php echo $question->id; ?>-selected", false);

                    // Open all.
                    ddtreemenu.flatten("comptree<?php echo $question->id; ?>-selected", "expand");
                }

                build_competence_output();
            });
            //]]>
        </script>
        <?php
    }

}
