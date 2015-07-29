<?php

use core_tests\event\static_info_viewing;
class block_exacomp_data {
    // TODO: change to protected
    public static function parse_sourceid($id) {
        if (!$id) return;
        
        $id = explode("::", $id);

        if (count($id) == 2) {
            return array('source' => $id[0], 'sourceid' => $id[1]);
        }
        
        die('todo parse_sourceid');
    }
    
    protected static function get_my_source() {
        global $CFG;
        return $CFG->wwwroot;
    }
    
    protected static function create_sourceid($item) {
        if (!$item) {
            return;
        }
        if ($item->source && $item->sourceid) {
            if ($item->source == IMPORT_SOURCE_DEFAULT) {
                // source und sourceid vorhanden -> von wo anders erhalten
                return 'default::'.$item->sourceid;
            } elseif ($item->source == IMPORT_SOURCE_SPECIFIC) {
                // local source -> von dieser moodle instanz selbst
                return self::get_my_source().'::'.$item->sourceid;
            }
            die('unknown source '.$item->source);
        } else {
            // local source -> set new id
            return self::get_my_source().'::'.$item->id;
        }
    }
    
}

class block_exacomp_data_exporter extends block_exacomp_data {
    
    public static function do_export($type = null /* TODO alles exportieren, nur aktuelles moodle exportieren... */) {
        global $DB;
        
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<exacomp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd" />'
        );
        
        $xml['source'] = self::get_my_source();
        
        // skills
        /*
            <skill id="1">
            <title><![CDATA[Hören]]></title>
            <sorting>6656</sorting>
            </skill>
        */
        /*
        $xmlSkills = $xml->addChild('skills');
        $dbSkills = $DB->get_records(DB_SKILLS);
        var_dump($dbSkills);
        foreach ($dbSkills as $dbSkill) {
            $xmlSkill = $xmlSkills->addChild('skill');
        }
        /*
        $skill->sourceid = $skill['id']->__toString();
$skill->source = IMPORT_SOURCE_DEFAULT;
    $DB->insert_record(DB_SKILLS, simpleXMLElementToArray($skill));
        */
    
        self::export_niveaus($xml);
        self::export_examples($xml);
        self::export_descriptors($xml);
        
        echo self::format_xml($xml);
        exit;
    }
    
    private static function export_niveaus($xmlParent, $parentid = 0) {
        global $DB;
        
        /*
        <niveau id="4">
            <title><![CDATA[B2]]></title>
            <sorting>5632</sorting>
            <niveautexts>
                <niveautext id="89" skillid="1" lang="de"><title><![CDATA[Ich kann...]]></title></niveautext>
            </niveautexts>
        </niveau>
        */
        $dbItems = $DB->get_records(DB_NIVEAUS, array('parentid'=>$parentid)); // , array("source"=>self::$source));
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'niveaus');
        
        // var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('niveau');
            $xmlItem['id'] = self::create_sourceid($dbItem);
            $xmlItem->title = $dbItem->title;
            
            // children
            self::export_niveaus($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_examples($xmlParent, $parentid = 0) {
        global $DB;
        
        /*
        <example id="3" taxid="78">
            <title><![CDATA[Hardware Anschaffungen]]></title>
            <titleshort><![CDATA[Hardware Anschaffungen]]></titleshort>
            <description><![CDATA[Zeitbedarf in Minuten:    30
    Hilfsmittel:    PC mit aktuellem Betriebssystem und MS Windows MovieMaker, Internet; Aufgabenstellung
    Didaktische Hinweise:    Teamarbeit mit 2-3 SchülerInnen. Teilen Sie jedem Team ein Thema zu.]]></description>
            <task><![CDATA[http://bist.edugroup.at/uploads/tx_exabiscompetences/Aufgabenstellung.pdf]]></task>
            <solution/>
            <attachement><![CDATA[http://bist.edugroup.at/uploads/tx_exabiscompetences/netbook_email.WMV]]></attachement>
            <completefile><![CDATA[http://bist.edugroup.at/uploads/tx_exabiscompetences/Gesamtbeispiel.zip]]></completefile>
            <tips/>
            <externalurl/>
            <externalsolution/>
            <externaltask/>
            <sorting>7936</sorting>
            <lang>0</lang>
            <iseditable>0</iseditable>
            <parentid>0</parentid>
            <epop>0</epop>
        </example>
        */
        // TODO: ignore user examples
        $dbItems = $DB->get_records_sql("
            SELECT e.*
            FROM {".DB_EXAMPLES."} e
            WHERE (sourceid IS NULL OR sourceid != ".EXAMPLE_SOURCE_USER.") AND
            ".($parentid ? "parentid = $parentid" : "(parentid=0 OR parentid IS NULL)")
        );
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'examples');

        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('example');
            $xmlItem['id'] = self::create_sourceid($dbItem);
            $xmlItem->title = $dbItem->title;
            $xmlItem->titleshort = $dbItem->titleshort;
            $xmlItem->description = $dbItem->description;
            
            // children
            self::export_examples($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_descriptors($xmlParent, $parentid = 0) {
        global $DB;
        
        /*
        <descriptors>
            <descriptor id="1" skillid="1" niveauid="2">
                <title><![CDATA[1.1. Ich kann Hardware-Komponenten unterscheiden und deren Funktionen erklären]]></title>
                <sorting>184320</sorting>
                <profoundness>0</profoundness>
                <epop>0</epop>
            <examples>
                <exampleid id="4"/>
                <exampleid id="3"/>
                <exampleid id="37"/>
                <exampleid id="38"/>
                <exampleid id="72"/>
                <exampleid id="126"/>
                <exampleid id="1957"/>
            </examples>
        </descriptor>
        */
        $dbItems = $DB->get_records(DB_DESCRIPTORS, array('parentid'=>$parentid));
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'descriptors');
        //var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('descriptor');
            $xmlItem['id'] = self::create_sourceid($dbItem);
            // TODO: skillid="1"
            if ($id = self::db_get_item_sourceid(DB_NIVEAUS, $dbItem->niveauid))
                $xmlItem['niveauid'] = $id;
            $xmlItem->title = $dbItem->title;
            $xmlItem->sorting= $dbItem->sorting;
            // TODO: <profoundness>0</profoundness>
            // TODO: <epop

            $examples = $DB->get_records_sql("
                SELECT e.*
                FROM {".DB_EXAMPLES."} e
                JOIN {".DB_DESCEXAMP."} de ON e.id = de.exampid
                WHERE de.descrid = ?
            ", array($dbItem->id));

            if ($examples) {
                $xmlExamples = $xmlItem->addChild('examples');
                foreach ($examples as $example) {
                    $xmlExample = $xmlExamples->addChild('example');
                    $xmlExample['id'] =  self::create_sourceid($example);
                }
            }
            
            // children
            self::export_descriptors($xmlItem, $dbItem->id);
        }

        return;
        
        /* TODO */
        if($descriptor['skillid'])
            $descriptor->skillid = $descriptor['skillid']->__toString();
    
        //if descriptor already in db, imported from same source -> update
        if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>$source))) {
            $descriptor->id = $descriptorObj->id;
            $descriptorarray = simpleXMLElementToArray($descriptor);
            if(!isset($descriptorarray['profoundness']))
                $descriptorarray['profoundness'] = 0;
    
            $DB->update_record(DB_DESCRIPTORS, $descriptorarray);
            $DB->delete_records(DB_DESCEXAMP,array("descrid" => $descriptor->id->__toString()));
        } else //descriptor not in db yet -> insert
            $descriptor->id = $DB->insert_record(DB_DESCRIPTORS, simpleXMLElementToArray($descriptor));
    }
    
    private static function db_get_item_sourceid($table, $id) {
        global $DB;
        return self::create_sourceid($DB->get_record($table, array("id" => $id)));
    }

    private static function format_xml($xml) {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}

class block_exacomp_data_importer extends block_exacomp_data {
    
    // TODO: change to private
    public static $import_source;
    
    /**
     *
     * @param String $data xml content
     * @param int $source default is 1, for specific import 2 is used. A specific import can be done by teachers and only effects data from topic leven downwards (topics, descriptors, examples)
     * @param int $cron should always be 0, 1 if method is called by the cron job
     */
    public static function do_import($data = null, $par_source = 1, $cron = false) {
        global $DB, $CFG;
    
        if($data == null)
            return false;
        
        self::$import_source = $par_source;
        /*
         * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
         * immediate useage
         */
        $xml = simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);
    
        if(isset($xml->table)){
            echo get_string('oldxmlfile', 'block_exacomp');
            return false;
        }
        if(self::$import_source == IMPORT_SOURCE_DEFAULT) {
            block_exacomp_xml_truncate(DB_SKILLS);
            if(isset($xml->skills)) {
                foreach($xml->skills->skill as $skill) {
                    block_exacomp_insert_skill($skill);
                }
            }
    
            //niveaus are only updated within normal import -> TODO
            self::insert_niveaus($xml->niveaus);
    
            block_exacomp_xml_truncate(DB_TAXONOMIES);
            if(isset($xml->taxonomies)) {
                foreach($xml->taxonomies->taxonomy as $taxonomy) {
                    block_exacomp_insert_taxonomy($taxonomy);
                }
            }
    
            if(isset($xml->categories)) {
                foreach($xml->categories->category as $category) {
                    block_exacomp_insert_category($category);
                }
            }
        }
        
        self::insert_examples($xml->examples);
    
        die('TODO');
        
        $crdate=time();
        if(isset($xml->descriptors)) {
            foreach($xml->descriptors->descriptor as $descriptor) {
                $descriptor->crdate = $crdate;
                block_exacomp_insert_descriptor($descriptor);
            }
        }
        if(isset($xml->crosssubjects)) {
            //insert empty draft as first entry
            block_exacomp_init_cross_subjects();
            foreach($xml->crosssubjects->crosssubject as $crosssubject) {
                block_exacomp_insert_crosssubject($crosssubject);
            }
        }
        $insertedTopics = array();
        foreach($xml->edulevels->edulevel as $edulevel) {
            if(self::$import_source == IMPORT_SOURCE_DEFAULT)
                block_exacomp_insert_edulevel($edulevel);
    
            foreach($edulevel->schooltypes->schooltype as $schooltype) {
                $schooltype->elid = $edulevel->id;
                block_exacomp_insert_schooltype($schooltype);
    
                foreach($schooltype->subjects->subject as $subject) {
                    $subject->stid = $schooltype->id;
                        block_exacomp_insert_subject($subject);
    
                    foreach($subject->topics->topic as $topic) {
                        $topic->subjid = $subject->id;
                        $insertedTopics[] = self::insert_topic($topic);
                    }
                }
            }
        }
    
        $founds = block_exacomp_xml_find_unused_descriptors(self::$import_source,$crdate,implode(",", $insertedTopics));
    
        //block_exacomp_deleteIfNoSubcategories("block_exacompdescrexamp_mm","block_exacompdescriptors","id",self::$import_source,1,0,"descrid");
        block_exacomp_deleteIfNoSubcategories("block_exacompexamples","block_exacompdescrexamp_mm","exampid",self::$import_source,0);
        //block_exacomp_deleteIfNoSubcategories("block_exacompdescrtopic_mm","block_exacompdescriptors","id",self::$import_source,1,0,"descrid");
        block_exacomp_deleteIfNoSubcategories("block_exacomptopics","block_exacompdescrtopic_mm","topicid",self::$import_source,0,implode(",", $insertedTopics));
        block_exacomp_deleteIfNoSubcategories("block_exacompsubjects","block_exacomptopics","subjid",self::$import_source);
        block_exacomp_deleteIfNoSubcategories("block_exacompschooltypes","block_exacompsubjects","stid",self::$import_source);
        block_exacomp_deleteIfNoSubcategories("block_exacompedulevels","block_exacompschooltypes","elid",self::$import_source);
    
        block_exacomp_settstamp();
        
        return true;
    }

    private static function insert_niveaus($xmlItems, $parent = 0) {
        global $DB;
        
        if (!$xmlItems->niveau) return;
        
        foreach ($xmlItems->niveau as $xmlItem) {
            $item = self::get_xml_item($xmlItem);
            // TODO: erweitern und überall reingeben
            $item = block_exacomp_clean_object($item, array(
                'source' => PARAM_TEXT,
                'sourceid' => PARAM_INT,
                'title' => PARAM_TEXT
            ));
            $item->parentid = $parent;

            if ($dbItem = $DB->get_record(DB_NIVEAUS, array('source'=>$item->source, 'sourceid'=>$item->sourceid))) {
                $item->id = $dbItem->id;
                $DB->update_record(DB_NIVEAUS, $item);
            } else {
                $item->id = $DB->insert_record(DB_NIVEAUS, $item);
            }
            
            self::insert_niveaus($xmlItem->children, $item->id);
        }
    }
    
    private static function insert_examples($xmlItems, $parent = 0) {
        global $DB;
        
        if (!$xmlItems->example) return;
        
        foreach ($xmlItems->example as $xmlItem) {
            $item = self::get_xml_item($xmlItem);
            $item->parentid = $parent;

            // TODO:
            // if($example['taxid'])
            // $example->taxid = block_exacomp_get_database_id(DB_TAXONOMIES,$example['taxid']->__toString(),block_exacomp_data_importer::$import_source);
    
            // TODO: brauchen wir das noch?
            /*
            if (self::$import_source != IMPORT_SOURCE_DEFAULT && $item->source == IMPORT_SOURCE_DEFAULT) {
                if ($exampleObj = $DB->get_record(DB_EXAMPLES, array("sourceid"=>$item->sourceid, "source" => $item->sourceid)))
                    return;
            }
            */
            
            if ($dbItem = $DB->get_record(DB_EXAMPLES, array("sourceid"=>$item->source, 'sourceid'=>$item->sourceid))) {
                $item->id = $dbItem->id;
                $DB->update_record(DB_EXAMPLES, $item);
            } elseif ($item->source == IMPORT_SOURCE_SPECIFIC && $dbItem = $DB->get_record(DB_EXAMPLES, array("id"=>$item->sourceid))) {
                $item->id = $dbItem->id;
                $DB->update_record(DB_EXAMPLES, $item);
            } else {
                $item->id = $DB->insert_record(DB_EXAMPLES, $item);
            }
    
            self::insert_examples($xmlItem->children, $item->id);
        }
    }
        
    private static function get_xml_item($xml) {
        $item = simpleXMLElementToArray($xml);
        if (isset($item['@attributes'])) {
            $item = $item['@attributes'] + $item;
            unset($item['@attributes']);
        }

        if (isset($item['id'])) {
            $item = self::parse_sourceid($item['id']) + $item;
            unset($item['id']);
        }
        
        if (isset($item['source']) && ($item['source'] === 'default')) {
            $item['source'] = IMPORT_SOURCE_DEFAULT;
        } elseif (isset($item['source']) && ($item['source'] === self::get_my_source())) {
            $item['source'] = IMPORT_SOURCE_SPECIFIC;
        } else {
            die('get_xml_item: wrong source '.print_r($item, true));
        }
        
        return (object)$item;
    }
}
    
function block_exacomp_insert_topic($topic, $parent = 0) {
    global $DB;
    $topic->sourceid = $topic['id']->__toString();
    $topic->parentid = $parent;

    if($stObj = $DB->get_record(DB_TOPICS, array("sourceid"=>$topic['id']->__toString()))) {
        $topic->id = $stObj->id;
        $DB->update_record(DB_TOPICS, simpleXMLElementToArray($topic));
    } else
        $topic->id = $DB->insert_record(DB_TOPICS, simpleXMLElementToArray($topic));

    if($topic->descriptors) {
        $DB->delete_records(DB_DESCTOPICS,array("topicid"=>$topic->id->__toString()));

        $i=1;
        foreach($topic->descriptors->descriptorid as $descriptor) {
            $descriptorid = $DB->get_field(DB_DESCRIPTORS, "id", array("sourceid"=>$descriptor['id']->__toString()));
            if($descriptorid > 0){
                $DB->insert_record(DB_DESCTOPICS, array("topicid"=>$topic->id->__toString(),"descrid"=>$descriptorid, "sorting"=>$i));
                $i++;
            }
        }
    }

    if($topic->children) {
        foreach($topic->children->topic as $child) {
            $child->subjid = $topic->subjid;
            block_exacomp_insert_topic($child,$topic->id);
        }
    }

    return $topic->id;
}
function block_exacomp_insert_subject(&$subject) {
    global $DB;
    
    $subject->sourceid = $subject['id']->__toString();
    //$subject->source = block_exacomp_data_importer::$import_source;
    if($subject['categoryid'])
        $subject->catid = block_exacomp_get_database_id(DB_CATEGORIES,$subject['categoryid']->__toString());

    if($stObj = $DB->get_record(DB_SUBJECTS, array("sourceid"=>$subject['id']->__toString()))) {
        $subject->id = $stObj->id;
        $DB->update_record(DB_SUBJECTS, simpleXMLElementToArray($subject));
    } else
        $subject->id = $DB->insert_record(DB_SUBJECTS, simpleXMLElementToArray($subject));
}
function block_exacomp_insert_schooltype(&$schooltype) {
    global $DB;
    
    if(block_exacomp_data_importer::$import_source > IMPORT_SOURCE_DEFAULT) {
         if($dbschooltype = $DB->get_record(DB_SCHOOLTYPES, array("sourceid"=>$schooltype['id']->__toString(),"source"=>IMPORT_SOURCE_DEFAULT)))
            $schooltype->id = $dbschooltype->id;
        
        return;
    }
    
    $schooltype->sourceid = $schooltype['id']->__toString();
    $schooltype->source = block_exacomp_data_importer::$import_source;

    if($stObj = $DB->get_record(DB_SCHOOLTYPES, array("sourceid"=>$schooltype['id']->__toString(),"source"=>block_exacomp_data_importer::$import_source))) {
        $schooltype->id = $stObj->id;
        $DB->update_record(DB_SCHOOLTYPES, simpleXMLElementToArray($schooltype));
    } else
        $schooltype->id = $DB->insert_record(DB_SCHOOLTYPES, simpleXMLElementToArray($schooltype));
}
function block_exacomp_insert_edulevel(&$edulevel) {
    global $DB;
    $edulevel->sourceid = $edulevel['id']->__toString();
    $edulevel->source = block_exacomp_data_importer::$import_source;

    if($eduObj = $DB->get_record(DB_EDULEVELS, array("sourceid"=>$edulevel['id']->__toString(),"source"=>block_exacomp_data_importer::$import_source))) {
        $edulevel->id = $eduObj->id;
        $DB->update_record(DB_EDULEVELS, simpleXMLElementToArray($edulevel));
    } else
        $edulevel->id = $DB->insert_record(DB_EDULEVELS, simpleXMLElementToArray($edulevel));
}

function block_exacomp_insert_descriptor($descriptor, $parent = 0, $sorting = 0) {
    global $DB;
    $descriptor->sourceid = $descriptor['id']->__toString();
    $descriptor->source = block_exacomp_data_importer::$import_source;
    
    if($parent > 0){
        $descriptor->parentid = $parent;
        $descriptor->sorting = $sorting;
    }
    
    if($descriptor['skillid'])
        $descriptor->skillid = $descriptor['skillid']->__toString();
    if($descriptor['niveauid']) //niveaus have to be imported with normal import -> TODO
        $descriptor->niveauid = block_exacomp_get_database_id(DB_NIVEAUS,$descriptor['niveauid']->__toString());
    if($descriptor['categoryid'])
        $descriptor->catid = block_exacomp_get_database_id(DB_CATEGORIES,$descriptor['categoryid']->__toString());
    
    //if specific import and descriptor already normal imported -> return
    if(block_exacomp_data_importer::$import_source != IMPORT_SOURCE_DEFAULT) {
        if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>IMPORT_SOURCE_DEFAULT)))
            return;
    }

    //other way round: if normale import and descriptor already specific imported -> return
    if(block_exacomp_data_importer::$import_source == IMPORT_SOURCE_DEFAULT){
        if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(), "source"=>IMPORT_SOURCE_SPECIFIC)))
            return;
    }
    
    //if descriptor already in db, imported from same source -> update
    if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>block_exacomp_data_importer::$import_source))) {
        $descriptor->id = $descriptorObj->id;
        $descriptorarray = simpleXMLElementToArray($descriptor);
        if(!isset($descriptorarray['profoundness']))
            $descriptorarray['profoundness'] = 0;
        
        $DB->update_record(DB_DESCRIPTORS, $descriptorarray);
        $DB->delete_records(DB_DESCEXAMP,array("descrid" => $descriptor->id->__toString()));
    } else //descriptor not in db yet -> insert
        $descriptor->id = $DB->insert_record(DB_DESCRIPTORS, simpleXMLElementToArray($descriptor));

    if($descriptor->examples) {
        foreach($descriptor->examples->exampleid as $example) {
            $exampleid = $DB->get_field(DB_EXAMPLES, "id", array("sourceid"=>$example['id']->__toString(),"source"=>block_exacomp_data_importer::$import_source));
            //$exampleid = $examples->xpath('example[@id="'.$example['id']->__toString().'"]');
            //$conditions = array("descrid"=>$descriptor->id->__toString(),"exampid"=>$exampleid[0]->id->__toString());
            $conditions = array("descrid"=>$descriptor->id->__toString(),"exampid"=>$exampleid);

            //if(!$DB->record_exists(DB_DESCEXAMP, $conditions)) //all records deleted above delete_records(DB_DESCEXA...
                $DB->insert_record(DB_DESCEXAMP, $conditions);
        }
    }
    
    if($descriptor->children) {
        $sorting = 1;
        foreach($descriptor->children->descriptor as $child){
            block_exacomp_insert_descriptor($child,$descriptor->id, $sorting);
            $sorting++;
        }
    }
}

function block_exacomp_insert_category($category, $parent = 0) {
    global $DB;
    $category->sourceid = $category['id']->__toString();
    $category->source = block_exacomp_data_importer::$import_source;
    $category->parentid = $parent;

    if($categoryObj = $DB->get_record(DB_CATEGORIES, array("sourceid"=>$category['id']->__toString(),"source" => block_exacomp_data_importer::$import_source))) {
        $category->id = $categoryObj->id;
        $DB->update_record(DB_CATEGORIES, simpleXMLElementToArray($category));
    } else
        $category->id = $DB->insert_record(DB_CATEGORIES, simpleXMLElementToArray($category));

    if($category->children) {
        foreach($category->children->category as $child)
            block_exacomp_insert_category($child,$category->id);
    }
}
function  block_exacomp_insert_crosssubject($crosssubject) {
    global $DB;
    
    $crosssubject->sourceid = $crosssubject['id']->__toString();
    $crosssubject->source = block_exacomp_data_importer::$import_source;
    
    if(block_exacomp_data_importer::$import_source != IMPORT_SOURCE_DEFAULT) {
        if($crosssubjectObj = $DB->get_record(DB_CROSSSUBJECTS, array("sourceid"=>$crosssubject['id']->__toString(), "source" => IMPORT_SOURCE_DEFAULT)))
            return;
    }
    
    if($crosssubjectObj = $DB->get_record(DB_CROSSSUBJECTS, array("sourceid"=>$crosssubject['id']->__toString(), "source" => block_exacomp_data_importer::$import_source))) {
        $crosssubject->id = $crosssubjectObj->id;
        $DB->update_record(DB_CROSSSUBJECTS, simpleXMLElementToArray($crosssubject));
    } else {
        $crosssubject->id = $DB->insert_record(DB_CROSSSUBJECTS, simpleXMLElementToArray($crosssubject));
    }
    
    //crosssubject in DB
    //insert descriptors
    
    if($crosssubject->descriptors) {
        $DB->delete_records(DB_DESCCROSS,array("crosssubjid"=>$crosssubject->id->__toString()));

        foreach($crosssubject->descriptors->descriptorid as $descriptor) {
            $descriptorid = $DB->get_field(DB_DESCRIPTORS, "id", array("sourceid"=>$descriptor['id']->__toString()));
            if($descriptorid > 0)
                $DB->insert_record(DB_DESCCROSS, array("crosssubjid"=>$crosssubject->id->__toString(),"descrid"=>$descriptorid));
        }
    }
    
    return $crosssubject->id;
}
function block_exacomp_insert_taxonomy($taxonomy, $parent = 0) {
    global $DB;
    $taxonomy->sourceid = $taxonomy['id']->__toString();
    $taxonomy->source = IMPORT_SOURCE_DEFAULT;
    $taxonomy->parentid = $parent;
    $id = $DB->insert_record(DB_TAXONOMIES, simpleXMLElementToArray($taxonomy));

    if($taxonomy->children) {
        foreach($taxonomy->children->taxonomy as $child) {
            block_exacomp_insert_taxonomy($child,$id);
        }
    }
}

function block_exacomp_insert_skill($skill) {
    global $DB;
    $skill->sourceid = $skill['id']->__toString();
    $skill->source = IMPORT_SOURCE_DEFAULT;
    $DB->insert_record(DB_SKILLS, simpleXMLElementToArray($skill));
}

/**
 * Moodle prohibits to use SimpleXML Objects as parameter values for $DB functions, therefore we need to convert
 * it to an array, which is done by encoding and decoding it as JSON.
 * Afterwards we need to filter the empty values, otherwise $DB functions throw warnings
 *
 * @param SimpleXMLElement $xmlobject
 */
function simpleXMLElementToArray(SimpleXMLElement $xmlobject) {
    $array = json_decode(json_encode((array)$xmlobject), true);
    $array_final = array();
    foreach($array as $key => $value){
        if(is_array($value) && empty($value)){
            $array_final[$key] = null;
        }else{
            $array_final[$key] = $value;
        }
    }
    return $array_final;
}

function block_exacomp_get_database_id($table, $sourceid, $par_source = 1) {
    global $DB;
    return $DB->get_field($table, "id", array("sourceid" => $sourceid, "source" => $par_source));
}

function block_exacomp_xml_truncate($tablename) {
    global $DB;
    $DB->delete_records($tablename, array("source" => block_exacomp_data_importer::$import_source));
}

/* this function deletes all categories if there are no subcategories
 i.e. if there are no topics to a subject, the subject can be deleted*/
function block_exacomp_deleteIfNoSubcategories($parenttable,$subtable,$subforeignfield,$source,$use_source_in_subtable=1,$pidlist="") {
    global $DB;
    $wherepid="";
    if ($use_source_in_subtable==1) $wheresource="source"; //zb source=1
    else $wheresource=$source; //zb 1=1
    if ($pidlist!="" AND $pidlist!="0") {
        $wherepid="AND (parentid NOT IN (".$pidlist.") OR parentid IS NULL)";
    }
    $sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE '.$wheresource.'=? AND '.$subforeignfield.'=pt.id)';
    $sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? '.$wherepid.' AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE '.$wheresource.'=?)';

    $todelets = $DB->get_records_sql($sql,array($source,$source));
    foreach ($todelets as $todelete) {
        $DB->delete_records($parenttable, array("id" => $todelete->id));
    }
}
function block_exacomp_xml_find_unused_descriptors($source,$crdate,$topiclist){
    global $DB;

    /* descriptoren löscent, wenn sie

    1) nicht im xml sind (crdate <> $crdate)
    2) nicht einer aktivität zugeordnet sind
    3) wenn es keine schüler/lehrer bewertung dazu direkt oder bei einer aktivität gibt
    4) wenn der zugehörige topic nirgends augewählt ist (bei settings/subjectselection)
    5) wenn der zugehörige schultyp nirgends augewählt ist (bei modulkonfiguration/schultypauswahl)
    6) wenn kein selbst hinaufgeladenes beispiel drannhängt
    */

    $sql="SELECT distinct descr.id,descr.sourceid FROM {block_exacompcompuser} u
    RIGHT JOIN {block_exacompdescriptors} descr ON descr.id=u.compid
    JOIN {block_exacompdescrtopic_mm} tmm ON tmm.descrid=descr.id
    JOIN {block_exacomptopics} top ON top.id=tmm.topicid
    JOIN {block_exacompsubjects} subj ON subj.id=top.subjid
    JOIN {block_exacompschooltypes} st ON st.id=subj.stid
    LEFT JOIN {block_exacompcoutopi_mm} cou ON cou.topicid=tmm.topicid
    LEFT JOIN ({block_exacompdescrexamp_mm} emm
    JOIN {block_exacompexamples} ex ON (ex.id=emm.exampid AND ex.source=3)) ON emm.descrid=descr.id
    LEFT JOIN {block_exacompmdltype_mm} typmm ON typmm.stid=st.id
    LEFT JOIN {block_exacompcompuser_mm} umm ON umm.compid=descr.id
    LEFT JOIN {block_exacompcompactiv_mm} act ON act.compid=descr.id
    WHERE typmm.id IS NULL AND ex.id IS NULL AND act.id IS NULL AND cou.id IS NULL AND  umm.id IS NULL AND u.id IS NULL AND descr.source=? AND descr.crdate <> (?)";

    $rs=$DB->get_records_sql($sql, array($source, $crdate));
    foreach($rs as $row){
        $DB->delete_records('block_exacompdescriptors', array("id" => $row->id));
        //topic, auch prüfen ob untertopics vorhanden, den dann nicht löschen
        $sql="DELETE FROM {block_exacompdescrtopic_mm} WHERE descrid=? AND topicid NOT IN (".$topiclist.")";
        $DB->Execute($sql, array($row->id));
        $DB->delete_records('block_exacompdescrexamp_mm', array("descrid" => $row->id));
    }
}

/**
 * checks if data is imported
 */
function block_exacomp_xml_check_import() {
    global $DB;
    return ($DB->get_records('block_exacompdescriptors')) ? true : false;
}
function block_exacomp_xml_check_custom_import() {
    global $DB;
    return ($DB->get_records(DB_DESCRIPTORS,array("source" => IMPORT_SOURCE_SPECIFIC))) ? true : false;
}
function block_exacomp_delete_custom_competencies() {
    global $DB;
    $DB->delete_records(DB_SUBJECTS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $DB->delete_records(DB_TOPICS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $DB->delete_records(DB_DESCRIPTORS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $examples = $DB->get_records(DB_EXAMPLES,array('source' => IMPORT_SOURCE_SPECIFIC));
    foreach($examples as $example) 
        block_exacomp_delete_custom_example($example->id);
    
    return true;
}

global $CFG;
require_once $CFG->libdir . '/formslib.php';

class block_exacomp_xml_upload_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;
        $mform = & $this->_form;

        $this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
        $mform->addElement('header', 'comment', get_string("doimport_own", "block_exacomp"));

        $mform->addElement('filepicker', 'file', get_string("file"),null);
        $mform->addRule('file', get_string("commentshouldnotbeempty", "block_exacomp"), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('add'));

    }

}

class block_exacomp_generalxml_upload_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;
        $mform = & $this->_form;

        $importtype = optional_param('importtype', 'normal', PARAM_TEXT);

        $this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
        $check = block_exacomp_xml_check_import();
        if($importtype == 'custom') {
            $mform->addElement('header', 'comment', get_string("doimport_own", "block_exacomp"));
        }
        elseif($check){
            $mform->addElement('header', 'comment', get_string("doimport", "block_exacomp"));
        } else
            $mform->addElement('header', 'comment', get_string("doimport_again", "block_exacomp"));


        $mform->addElement('filepicker', 'file', get_string("file"),null);
        $mform->addRule('file', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('add'));

    }

}