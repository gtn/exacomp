<?php
function print_profile_settings($courses, $settings, $exaport, $exastud, $exaport_items, $exastud_periods){
	
	$exacomp_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exacomp'));
	$exacomp_div_content .= html_writer::div(
		html_writer::checkbox('showonlyreached', 1, ($settings->showonlyreached==1), get_string('profile_settings_showonlyreached', 'block_exacomp')));
	
	$content_courses = html_writer::label(get_string('profile_settings_choose_courses', 'block_exacomp'), '');
	foreach($courses as $course){
		$content_courses .= html_writer::checkbox('profile_settings_course[]', $course->id, (isset($settings->exacomp[$course->id])), $course->fullname);
	}
	$exacomp_div_content .= html_writer::div($content_courses);
	$exacomp_div = html_writer::div($exacomp_div_content);
	
	$content .= $exacomp_div;
	
	if($exaport){
		$exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
		$exaport_div_content .= html_writer::div(
			html_writer::checkbox('useexaport', 1, ($settings->useexaport==1), get_string('profile_settings_useexaport', 'block_exacomp')));
		
		if($settings->useexaport == 1){
			$content_items = html_writer::label(get_string('profile_settings_choose_items', 'block_exacomp'), '');
			foreach($exaport_items as $item){
				$content_items .= html_writer::checkbox('profile_settings_items[]', $item->id, (isset($settings->exaport[$item->id])), $item->name);
			}
			$exacomp_div_content .= html_writer::div($content_items);
		}
		
		$exaport_div = html_writer::div($exaport_div_content);
		$content .= $exaport_div;
	}
	
	if($exastud){
		$exastud_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exastud'));
		$exasutd_div_content .= html_writer::div(
			html_writer::checkbox('useexastud', 1, ($settings->useexastud ==1), get_string('profile_settings_useexastud', 'block_exacomp')));
		
		if($settings->useexastud == 1){
			$content_periods = html_writer::label(get_string('profile_settings_choose_periods', 'block_exacomp'), '');
			foreach($exastud_periods as $period){
				$content_periods .= html_writer::checkbox('profile_settings_periods[]', $period->id, (isset($settings->exastud[$period->id])), $period->title);
			}
			$exastud_div_content .= html_writer::div($content_periods);
		}
		$exastud_div = html_writer::div($exastud_div_content);
		$content .= $exastud_div;
	}
	
	$content .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp')));
	
	$div = html_writer::div(html_writer::tag('form',
				$content,
				array('action'=>'competence_profile_settings.php?courseid='.$courseid, 'method'=>'post')), 'block_excomp_center');

	return html_writer::tag("div", $div, array("id"=>"exabis_competences_block"));
			
	return $content;
}

?>