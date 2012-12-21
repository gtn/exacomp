<?php
require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $COURSE, $CFG, $OUTPUT, $USER;
$content = "";
$courseid = required_param('courseid', PARAM_INT);
$sort = optional_param('sort', "desc", PARAM_ALPHA);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:teacher', $context);

$url = '/blocks/exacomp/view_examples.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
$identifier = "teachertabassigncompetenceexamples";
block_exacomp_print_header("teacher", $identifier);
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';
echo '<script type="text/javascript" src="lib/simpletreemenu.js"></script>';

?>
<h4><?php echo get_string("examples", "block_exacomp") ?></h4>
<?php echo get_string("sorting", "block_exacomp") ?> <a href="<?php echo $url.'&amp;sort=desc';?>"><?php echo ($sort=="desc") ? "<b>".get_string("subject", "block_exacomp")."</b>" : get_string("subject", "block_exacomp")?></a>, <a href="<?php echo $url.'&amp;sort=tax';?>"><?php echo ($sort=="tax") ? "<b>".get_string("taxonomies", "block_exacomp")."</b>" : get_string("taxonomies", "block_exacomp")?></a>
<br/><br/>

<a href="javascript:ddtreemenu.flatten('comptree', 'expand')"><?php echo get_string("expandcomps", "block_exacomp") ?></a> | <a href="javascript:ddtreemenu.flatten('comptree', 'contact')"><?php echo get_string("contactcomps", "block_exacomp") ?></a>

<?php echo block_exacomp_build_comp_tree($courseid,$sort); ?>


<script type="text/javascript">
    ddtreemenu.createTree("comptree", true)
</script>


<?php
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();
/*
  $content.='<div class="grade-report-grader">
  <table id="comps" class="compstable flexible boxaligncenter generaltable">
  <tr class="heading r0">
  <td class="category catlevel1" colspan="2" scope="col"><h2>' . $COURSE->fullname . '</h2></td></tr>';
  $descriptors = block_exacomp_get_examples($courseid);
  $trclass = "even";
  $topic = "";
  foreach ($descriptors as $descriptor) {
  if ($trclass == "even") {
  $trclass = "odd";
  $bgcolor = ' style="background-color:#efefef" ';
  } else {
  $trclass = "even";
  $bgcolor = ' style="background-color:#ffffff" ';
  }

  if ($topic !== $descriptor->topic) {
  $topic = $descriptor->topic;
  $content .= '<tr><td colspan="2"><b>' . $topic . '</b></tr>';
  }
  $content .= '<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td>'.$descriptor->title.'</td>';
  if(isset ($descriptor->examples))
  $examples = $descriptor->examples;
  if(isset($examples)) {
  $content .= '<td>';
  foreach($examples as $example) {
  $icon = block_exacomp_get_exampleicon($example);
  $content .= $icon;
  }
  $content .= '</td></tr>';
  unset($examples);
  }
  else {
  $content .= '<td></td></tr>';
  }
  }
  $content.="</table></div>";
  echo $content; */
?>
