<?php 

function print_competence_profile_exaport($settings, $user, $items){
	$content .= html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
	
	//print items with comps
	foreach($items as $item){
		if($item->hascomps)
			$content .= $this->print_exaport_item($item);
	}
	
	return $content;
}

function print_exaport_item($item){
	$li_item .= html_writer::tag('li', $item->name, array('class'=>'competence_profile_item'));
	
	$li_descriptors = '';
	foreach($item->descriptors as $descriptor) {
			$class = 'competence_profile_descriptor';
			$li_descriptors .= '<li class="'.$class.'">' . $descriptor->title	 . '</li>';
	}
	$ul_descriptors = html_writer::tag('ul', $li_descriptors);
	$li_item .= $ul_descriptors;
	return html_writer::tag('ul', $li_item);	
}
function print_competence_profile_exastud($settings, $user, $courses){
	
}
	?>