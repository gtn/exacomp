<?php

namespace core_question\bank;
defined('MOODLE_INTERNAL') || die();
require_once('../exaport/lib/lib.php');

class descriptor_link_column extends column_base {
    public function get_name() {
        return 'descriptorlink';
    }

    protected function get_title() {
        return '';
    }

    protected function display_content($question, $rowclasses) {
        global $USER, $DB;
        echo '<a href="#" class=" btn btn-primary btn-sm competences' . $question->id . '" role="button"> ' . block_exacomp_get_string("questlink") . ' </a>';

        $conditions = array("id" => 1, "userid" => $USER->id);
        $existing = $DB->get_record('block_exaportitem', $conditions)

        ?>
        <div style="display: none">
            <div id="inline_comp_tree" class="ict<?php echo $question->id; ?>" style='padding: 10px; background: #fff;'>
                <h4>
                    <?php echo block_exacomp_get_string("opencomps") ?>
                </h4>

                <a href="javascript:ddtreemenu.flatten('comptree<?php echo $question->id; ?>', 'expand')"><?php echo block_exacomp_get_string("expandcomps") ?>
                </a> | <a href="javascript:ddtreemenu.flatten('comptree<?php echo $question->id; ?>', 'contact')"><?php echo block_exacomp_get_string("contactcomps") ?>
                </a>

                <?php echo block_exacomp_build_comp_tree($question); ?>
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

    public function get_required_fields() {
        return array('q.id');
    }
}
