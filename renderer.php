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

/**
 * exacomp block rendrer
 *
 * @package    block_exacomp
 * @copyright  2013 gtn gmbh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class block_exacomp_renderer extends plugin_renderer_base {

	public function render_competence_grid($niveaus, $skills, $topics, $data) {
		$table = new html_table();
		$table->attributes['class'] = 'competence_grid';
		$head = array();
		$head[] = "";
		$head[] = "";
		$head = array_merge($head,$niveaus);
		$table->head = $head;

		$rows = array();
		foreach($data as $skillid => $skill) {
			$row = new html_table_row();
			$cell1 = new html_table_cell();
			$cell1->text = html_writer::tag("p",$skills[$skillid]);
			$cell1->attributes['class'] = 'skill';
			$cell1->rowspan = count($skill);
			$row->cells[] = $cell1;

			foreach($skill as $topicid => $topic) {
				$cell2 = new html_table_cell();
				$cell2->text = html_writer::tag("p",$topics[$topicid]);
				$cell2->attributes['class'] = 'topic';
				$row->cells[] = $cell2;

				foreach($niveaus as $niveauid => $niveau) {
					if(isset($data[$skillid][$topicid][$niveauid])) {
						$compString = "";
						foreach($data[$skillid][$topicid][$niveauid] as $descriptor)
							$compString .= $descriptor->title;

						$row->cells[] = $compString;
					} else
						$row->cells[] = "";
				}
			}
			$rows[] = $row;
		}
		$table->data = $rows;

		return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
	}
	public function exacomp_tree(settings_navigation $navigation) {
		$count = 0;
		foreach ($navigation->children as &$child) {
			$child->preceedwithhr = ($count!==0);
			$count++;
		}
		$content = $this->navigation_node($navigation, array('class'=>'block_tree list'));
		if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
			$content = $this->output->box($content, 'block_tree_box', $navigation->id);
		}
		return $content;
	}

	protected function navigation_node(navigation_node $node, $attrs=array()) {
		$items = $node->children;

		// exit if empty, we don't want an empty ul element
		if ($items->count()==0) {
			return '';
		}

		// array of nested li elements
		$lis = array();
		foreach ($items as $item) {
			if (!$item->display) {
				continue;
			}

			$isbranch = ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH);
			$hasicon = (!$isbranch && $item->icon instanceof renderable);

			if ($isbranch) {
				$item->hideicon = true;
			}
			$content = $this->output->render($item);

			// this applies to the li item which contains all child lists too
			$liclasses = array($item->get_css_type());
			if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count()==0  && $item->nodetype==navigation_node::NODETYPE_BRANCH)) {
				$liclasses[] = 'collapsed';
			}
			if ($isbranch) {
				$liclasses[] = 'contains_branch';
			} else if ($hasicon) {
				$liclasses[] = 'item_with_icon';
			}
			if ($item->isactive === true) {
				$liclasses[] = 'current_branch';
			}
			$liattr = array('class'=>join(' ',$liclasses));
			// class attribute on the div item which only contains the item content
			$divclasses = array('tree_item');
			if ($isbranch) {
				$divclasses[] = 'branch';
			} else {
				$divclasses[] = 'leaf';
			}
			if (!empty($item->classes) && count($item->classes)>0) {
				$divclasses[] = join(' ', $item->classes);
			}
			$divattr = array('class'=>join(' ', $divclasses));
			if (!empty($item->id)) {
				$divattr['id'] = $item->id;
			}
			$content = html_writer::tag('p', $content, $divattr) . $this->navigation_node($item);
			if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
				$content = html_writer::empty_tag('hr') . $content;
			}
			$content = html_writer::tag('li', $content, $liattr);
			$lis[] = $content;
		}

		if (count($lis)) {
			return html_writer::tag('ul', implode("\n", $lis), $attrs);
		} else {
			return '';
		}
	}


}