<?php
// This file is part of Exabis Competence Grid
//
// (c) 2019 GTN - Global Training Network GmbH <office@gtn-solutions.com>
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

require __DIR__.'/inc.php';
require_once __DIR__.'/update_categories_form.php';
// require_once __DIR__.'/example_upload_form.php';

$courseid = required_param('courseid', PARAM_INT);
$descrid = required_param('descrid', PARAM_INT);

require_login($courseid);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);


/* PAGE URL - MUST BE CHANGED */ //why though? RW
$PAGE->set_url('/blocks/exacomp/update_categories.php', array('courseid' => $courseid));
$PAGE->set_title(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');


// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */
$categories = $DB->get_records_menu(BLOCK_EXACOMP_DB_CATEGORIES,null,"","id, title");
$form = new block_exacomp_update_categories_form($_SERVER['REQUEST_URI'],
    array("descrid" => $descrid,"categories"=>$categories));
$item = $descrid ? \block_exacomp\descriptor::get($descrid) : null;

if ($formdata = $form->get_data()) {

    //insert catids in BLOCK_EXACOMP_DB_DESCCAT
//     $DB->delete_records(BLOCK_EXACOMP_DB_DESCCAT, ['descrid' => $descrid]);
//     if (!empty($formdata->catid)) {
//         foreach($formdata->catid as $cat => $catid)
//             $DB->insert_record(BLOCK_EXACOMP_DB_DESCCAT, [
//                 'descrid' => $descrid,
//                 'catid' => $catid
//             ]);
//     }
    $item->store_categories($formdata->catid);

    // or create a new category from example form
    $newCat = trim(optional_param('newcategory', '', PARAM_RAW));
    if ($newCat != '') {
        $newCategory = new \stdClass();
        $newCategory->title = $newCat;
        $newCategory->parentid = 0;
        $newCategory->sorting = $DB->get_field(BLOCK_EXACOMP_DB_CATEGORIES, 'MAX(sorting)', array()) + 1;
        $newCategory->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER; //RW what does source mean here?
        $newCategory->sourceid = 0;
        $newCategory->lvl = 5;
        $newCategory->id = $DB->insert_record(BLOCK_EXACOMP_DB_CATEGORIES, $newCategory);
        $DB->insert_record(BLOCK_EXACOMP_DB_DESCCAT, [
            'descrid' => $descrid,
            'catid' => $newCategory->id
        ]);
    }

    echo $output->popup_close_and_reload();
    exit;
}else if($form->is_cancelled()){
    echo $output->popup_close_and_reload();
    exit;
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();