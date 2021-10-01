<?php


namespace core_question\bank;
defined('MOODLE_INTERNAL') || die();
require_once('../exaport/lib/lib.php');


class descriptor_link_column extends column_base
{
    public function get_name()
    {
        return 'descriptorlink';
    }

    protected function get_title()
    {
        return get_string('createdby', 'question');
    }

    protected function display_content($question, $rowclasses)
    {
    global $USER, $DB;
    echo'<a href="#" class="competences"> Link </a>';

        $conditions = array("id" => 1, "userid" => $USER->id);
        $existing = $DB->get_record('block_exaportitem', $conditions)


        ?>
        <div style="display: none">
            <div id='inline_comp_tree' style='padding: 10px; background: #fff;'>
                <h4>
                    <?php echo get_string("opencomps", "block_exaport") ?>
                </h4>

                <a href="javascript:ddtreemenu.flatten('comptree', 'expand')"><?php echo get_string("expandcomps", "block_exaport") ?>
                </a> | <a href="javascript:ddtreemenu.flatten('comptree', 'contact')"><?php echo get_string("contactcomps",
                        "block_exaport") ?>
                </a>

                <?php echo block_exacomp_build_comp_tree($question); ?>
            </div>
        </div>

        <script type="text/javascript">
            //<![CDATA[
            jQueryExacomp(function ($) {

                $("#treeform :checkbox").click(function (e) {
                    // Prevent item open/close.
                    e.stopPropagation();
                });

                var $compids = $("input[name=compids]");
                var $descriptors = $("#treeform :checkbox");

                $(".competences").colorbox({
                    width: "75%", height: "75%", inline: true, href: "#inline_comp_tree", onClosed: function () {
                        // Save ids to input field.
                        var compids = "";
                        $descriptors.filter(":checked").each(function () {
                            compids += this.value + ", ";
                        });
                        $compids.val(compids);

                        build_competence_output();
                    }
                });
                ddtreemenu.createTree("comptree", true);

                function build_competence_output() {
                    var $tree = $("#comptree").clone();
                    // Remove original id, conflicts with real tree.
                    $tree.attr("id", "comptree-selected");

                    // Delete all not checked.
                    $tree.find("li").each(function () {
                        if (!$(this).find(":checked").length) {
                            $(this).remove();
                        }
                    });

                    // Delete checkboxes.
                    $tree.find(":checkbox").remove();

                    $("#comptitles").empty().append($tree);
                    ddtreemenu.createTree("comptree-selected", false);

                    // Open all.
                    ddtreemenu.flatten("comptree-selected", "expand");
                }

                build_competence_output();
            });
            //]]>
        </script>
        <?php

    }

    public function get_required_fields()
    {
        return array('q.id');
    }
}
