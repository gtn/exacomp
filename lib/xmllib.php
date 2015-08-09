<?php

class block_exacomp_data {

    protected static $sourceTables = array(DB_SKILLS, DB_NIVEAUS, DB_TAXONOMIES, DB_CATEGORIES, DB_EXAMPLES,
                    DB_DESCRIPTORS, DB_CROSSSUBJECTS, DB_EDULEVELS, DB_SCHOOLTYPES, DB_SUBJECTS,
                    DB_TOPICS);
    
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
            } elseif ($source = self::get_source_global_id($item->source)) {
                return $source.'::'.$item->sourceid;
            }
            
            print_error('database error, unknown source '.$item->source);
        } else {
            // local source -> set new id
            return self::get_my_source().'::'.$item->id;
        }
    }

    
    
    
    
    private static $sources = null; // array(local_id => global_id)
    const DUMMY_SOURCE_ID = 100;
    
    protected static function get_source_global_id($source_local_id) {
        self::load_sources();

        return isset(self::$sources[$source_local_id]) ? self::$sources[$source_local_id] : null;
    }
    
    protected static function add_source_if_not_exists($source_global_id) {
        self::load_sources();
        
        if ($source_local_id = array_search($source_global_id, self::$sources)) {
            return $source_local_id;
        }
        
        global $DB;
        // add dummy source, so sources start at a higher id
        if (!isset(self::$sources[self::DUMMY_SOURCE_ID])) {
            $DB->execute("INSERT INTO {block_exacompdatasources} (id, source) VALUES (".self::DUMMY_SOURCE_ID.", 'dummy source')");
        }
        
        // add new source
        $source_local_id = $DB->insert_record("block_exacompdatasources", array('source' => $source_global_id));
        
        self::$sources[$source_local_id] = $source_global_id;
        
        return $source_local_id;
    }
    
    private static function load_sources() {
        global $DB;
        
        if (self::$sources === null) {
            self::$sources = $DB->get_records_sql_menu("
                SELECT id, source AS global_id
                FROM {block_exacompdatasources}
            ");
        }
        
        return self::$sources;
    }

    /**
     * checks if data is imported
     */
    public static function has_data() {
        global $DB;
        
        return (bool)$DB->get_records('block_exacompdescriptors', array(), null, 'id', 0, 1);
    }
    /*
     * check if there is still data in the old source format
     */
    public static function has_old_data($source) {
        global $DB;
        
        return (bool)$DB->get_records('block_exacompdescriptors', array("source" => $source), null, 'id', 0, 1);
    }
    protected static function move_items_to_source($oldSource, $newSource) {
        global $DB;
        
        foreach (self::$sourceTables as $table) {
            $DB->execute("UPDATE {{$table}} SET source=? WHERE source=?", array($newSource, $oldSource));
        }
    }
    
    public static function delete_source($source) {
        global $DB;
        
        foreach (self::$sourceTables as $table) {
            $DB->delete_records($table, array('source' => $source));
        }
        
        $DB->delete_records("block_exacompdatasources", array('id' => $source));

        return true;
    }
    /*
    public static function delete_custom_competencies() {
        global $DB;
        
        // TODO: geht so nicht mehr
        $DB->delete_records(DB_SUBJECTS,array('source' => IMPORT_SOURCE_SPECIFIC));
        $DB->delete_records(DB_TOPICS,array('source' => IMPORT_SOURCE_SPECIFIC));
        $DB->delete_records(DB_DESCRIPTORS,array('source' => IMPORT_SOURCE_SPECIFIC));
        $examples = $DB->get_records(DB_EXAMPLES,array('source' => IMPORT_SOURCE_SPECIFIC));
        foreach($examples as $example) 
            block_exacomp_delete_custom_example($example->id);
        
        return true;
    }
    */
    
}

class block_exacomp_data_exporter extends block_exacomp_data {
    
    public static function do_export($type = null /* TODO alles exportieren, nur aktuelles moodle exportieren... */) {
        global $DB;
        
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);
        
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<exacomp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd" />'
        );
        
        $xml['source'] = self::get_my_source();
        
        // TODO: skills
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
        self::export_edulevels($xml);
        
        header('Content-Type: text/xml');
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
            $xmlItem->sorting = $dbItem->sorting;
            
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

        // ignore user examples
        $dbItems = $DB->get_records_sql("
            SELECT e.*
            FROM {".DB_EXAMPLES."} e
            WHERE (source IS NULL OR source != ".EXAMPLE_SOURCE_USER.") AND
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
            $xmlItem->sorting = $dbItem->sorting;
            $xmlItem->profoundness = $dbItem->profoundness;
            $xmlItem->epop = $dbItem->epop;

            $examples = $DB->get_records_sql("
                SELECT e.id, e.source, e.sourceid
                FROM {".DB_EXAMPLES."} e
                JOIN {".DB_DESCEXAMP."} de ON e.id = de.exampid
                WHERE de.descrid = ?
                GROUP BY e.id, e.source, e.sourceid
            ", array($dbItem->id));

            if ($examples) {
                $xmlExamples = $xmlItem->addChild('examples');
                foreach ($examples as $example) {
                    $xmlExample = $xmlExamples->addChild('exampleid');
                    $xmlExample['id'] =  self::create_sourceid($example);
                }
            }
            
            // children
            self::export_descriptors($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_edulevels($xmlParent, $parentid = 0) {
        global $DB;
        
        // TODO: definieren was wir exportieren
        return;
    
        // TODO: nur jene exportieren, welche in dieser schule verwendet werden?
        $dbEdulevels = block_exacomp_get_edulevels();
        
        foreach ($dbEdulevels as $dbEdulevel) {
            var_dump($dbEdulevels);
            $schooltypes = block_exacomp_get_schooltypes($dbEdulevel->id);
            var_dump($schooltypes);
        }
        
        return;
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'descriptors');
        //var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('descriptor');
            $xmlItem['id'] = self::create_sourceid($dbItem);
            // TODO: skillid="1"
        }
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
    
    private static $import_source_type;
    private static $import_source_global_id;
    private static $import_source_local_id;
    
    private static $import_time = null;
    
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
        
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);
        
        // TODO: source typ haben wir dann nicht mehr
        self::$import_source_type = $par_source;
        self::$import_time = time();
        /*
         * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
         * immediate useage
         */
        $xml = simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);
    
        if(isset($xml->table)){
            echo get_string('oldxmlfile', 'block_exacomp');
            return false;
        }
        
        if (empty($xml['source'])) {
            echo get_string('oldxmlfile', 'block_exacomp');
            return false;
        }
        
        self::$import_source_global_id = (string)$xml['source'];
        self::$import_source_local_id = self::add_source_if_not_exists(self::$import_source_global_id);
        
        // save source name
        $DB->update_record("block_exacompdatasources", array('id'=>self::$import_source_local_id, 'name'=>(string)$xml['sourcename'], 'type'=>self::$import_source_type));
        
        
        // update scripts for new source format
        if (self::has_old_data(IMPORT_SOURCE_DEFAULT)) {
            if (self::$import_source_type != IMPORT_SOURCE_DEFAULT) {
                print_error('you first need to import the default sources!');
            }
            self::move_items_to_source(IMPORT_SOURCE_DEFAULT, self::$import_source_local_id);
        }
        else {
            // always move old specific data
            self::move_items_to_source(IMPORT_SOURCE_SPECIFIC, self::$import_source_local_id);
        }
        
        self::kompetenzraster_load_current_data_for_source();
        
        
        if(isset($xml->skills)) {
            foreach($xml->skills->skill as $skill) {
                self::insert_skill($skill);
            }
        }

        if(isset($xml->niveaus)) {
            foreach($xml->niveaus->niveau as $niveau) {
                self::insert_niveau($niveau);
            }
        }
        
        if(isset($xml->taxonomies)) {
            foreach($xml->taxonomies->taxonomy as $taxonomy) {
                self::insert_taxonomy($taxonomy);
            }
        }

        if(isset($xml->categories)) {
            foreach($xml->categories->category as $category) {
                self::insert_category($category);
            }
        }
        
        if (isset($xml->examples)) {
            foreach($xml->examples->example as $example) {
                self::insert_example($example);
            }
        }
        
        if (isset($xml->descriptors)) {
            foreach($xml->descriptors->descriptor as $descriptor) {
                self::insert_descriptor($descriptor);
            }
        }
        
        if(isset($xml->crosssubjects)) {
            //insert empty draft as first entry
            block_exacomp_init_cross_subjects();
            foreach($xml->crosssubjects->crosssubject as $crosssubject) {
                self::insert_crosssubject($crosssubject);
            }
        }

        $insertedTopics = array();
        if(isset($xml->edulevels)) {
            foreach($xml->edulevels->edulevel as $edulevel) {
                $dbEdulevel = self::insert_edulevel($edulevel);
        
                foreach($edulevel->schooltypes->schooltype as $schooltype) {
                    $schooltype->elid = $dbEdulevel->id;
                    $dbSchooltype = self::insert_schooltype($schooltype);
        
                    foreach($schooltype->subjects->subject as $subject) {
                        $subject->stid = $dbSchooltype->id;
                        $dbSubject = self::insert_subject($subject);
        
                        foreach($subject->topics->topic as $topic) {
                            $topic->subjid = $dbSubject->id;
                            $insertedTopics[] = self::insert_topic($topic)->id;
                        }
                    }
                }
            }
        }
        
        // self::kompetenzraster_clean_unused_data_from_source();
    
        self::delete_unused_descriptors(self::$import_source_local_id, self::$import_time, implode(",", $insertedTopics));
    
        //self::deleteIfNoSubcategories("block_exacompdescrexamp_mm","block_exacompdescriptors","id",self::$import_source_local_id,1,0,"descrid");
        self::deleteIfNoSubcategories("block_exacompexamples","block_exacompdescrexamp_mm","exampid",self::$import_source_local_id,0);
        //self::deleteIfNoSubcategories("block_exacompdescrtopic_mm","block_exacompdescriptors","id",self::$import_source_local_id,1,0,"descrid");
        self::deleteIfNoSubcategories("block_exacomptopics","block_exacompdescrtopic_mm","topicid",self::$import_source_local_id,0,implode(",", $insertedTopics));
        self::deleteIfNoSubcategories("block_exacompsubjects","block_exacomptopics","subjid",self::$import_source_local_id);
        self::deleteIfNoSubcategories("block_exacompschooltypes","block_exacompsubjects","stid",self::$import_source_local_id);
        self::deleteIfNoSubcategories("block_exacompedulevels","block_exacompschooltypes","elid",self::$import_source_local_id);
    
        block_exacomp_settstamp();
        
        return true;
    }

    
    
    
    
    
    
    
    private static function insert_or_update_item($table, $item) {
        global $DB;
        
        if ($dbItem = $DB->get_record($table, array('source'=>$item->source, 'sourceid'=>$item->sourceid))) {
            $item->id = $dbItem->id;
            $DB->update_record($table, $item);
        } else {
            $item->id = $DB->insert_record($table, $item);
        }
    }
    
    private static function insert_niveau($xmlItem, $parent = 0) {
        $item = self::parse_xml_item($xmlItem);

        // TODO: check erweitern und überall reingeben
        /*
        $item = block_exacomp_clean_object($item, array(
            'source' => PARAM_TEXT,
            'sourceid' => PARAM_INT,
            'title' => PARAM_TEXT
        ));
        */
        $item->parentid = $parent;
        
        self::insert_or_update_item(DB_NIVEAUS, $item);
        self::kompetenzraster_mark_item_used(DB_NIVEAUS, $item);
        
        if ($xmlItem->children) {
            foreach ($xmlItem->children->niveau as $child) {
                self::insert_niveau($child, $item->id);
            }
        }
        
        return $item;
    }
    
    private static function insert_example($xmlItem, $parent = 0) {
        global $DB;
        
        $item = self::parse_xml_item($xmlItem);
        $item->parentid = $parent;
        
        if ($xmlItem->taxonomyid) {
            $item->taxid = self::get_database_id($xmlItem->taxonomyid); 
        }

        self::insert_or_update_item(DB_EXAMPLES, $item);
        self::kompetenzraster_mark_item_used(DB_EXAMPLES, $item);
        
        if ($xmlItem->descriptors) {
            if ($item->source == self::$import_source_global_id)
                $DB->delete_records(DB_DESCEXAMP,array("exampid"=>$item->id));

            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    $DB->insert_record(DB_DESCEXAMP, array("exampid"=>$item->id, "descrid"=>$descriptorid));
                }
            }
        }
        
        if ($xmlItem->children) {
            foreach ($xmlItem->children->example as $child) {
                self::insert_example($child, $item->id);
            }
        }
        
        return $item;
    }
    
    private static function insert_category($xmlItem, $parent = 0) {
        global $DB;
        
        $item = self::parse_xml_item($xmlItem);
        
        $item->parentid = $parent;
    
        self::insert_or_update_item(DB_CATEGORIES, $item);
        self::kompetenzraster_mark_item_used(DB_CATEGORIES, $item);
        
        // OLD:
        /*
        if ($dbItem = $DB->get_record(DB_EXAMPLES, array("sourceid"=>$item->source, 'sourceid'=>$item->sourceid))) {
            $item->id = $dbItem->id;
            $DB->update_record(DB_EXAMPLES, $item);
        } elseif ($item->source == IMPORT_SOURCE_SPECIFIC && $dbItem = $DB->get_record(DB_EXAMPLES, array("id"=>$item->sourceid))) {
            $item->id = $dbItem->id;
            $DB->update_record(DB_EXAMPLES, $item);
        } else {
            $item->id = $DB->insert_record(DB_EXAMPLES, $item);
        }
        */
        
        if ($xmlItem->children) {
            foreach($xmlItem->children->category as $child) {
                self::insert_category($child, $item->id);
            }
        }
        
        return $item;
    }
        
    private static function insert_descriptor($xmlItem, $parent = 0, $sorting = 0) {
        global $DB;
        
        $descriptor = self::parse_xml_item($xmlItem);
        $descriptor->crdate = self::$import_time;
        
        if ($parent > 0){
            $descriptor->parentid = $parent;
            $descriptor->sorting = $sorting;
        }
        
        if ($xmlItem->niveauid)
            $descriptor->niveauid = self::get_database_id($xmlItem->niveauid);
        if ($xmlItem->categoryid)
            $descriptor->catid = self::get_database_id($xmlItem->categoryid);
        if ($xmlItem->skillid)
            $descriptor->skillid = self::get_database_id($xmlItem->skillid);
        if (!isset($descriptor->profoundness))
            $descriptor->profoundness = 0;
        
        // brauchen wir nicht mehr:
        /*
        //if specific import and descriptor already normal imported -> return
        if(block_exacomp_data_importer::$import_source_type != IMPORT_SOURCE_DEFAULT) {
            if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>IMPORT_SOURCE_DEFAULT)))
                return;
        }
    
        //other way round: if normale import and descriptor already specific imported -> return
        if(block_exacomp_data_importer::$import_source_type == IMPORT_SOURCE_DEFAULT){
            if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(), "source"=>IMPORT_SOURCE_SPECIFIC)))
                return;
        }
        
        */
        
        self::insert_or_update_item(DB_DESCRIPTORS, $descriptor);
        self::kompetenzraster_mark_item_used(DB_DESCRIPTORS, $descriptor);
        
        if ($xmlItem->examples) {
            print_error('wrong format');
        }
        
        if ($xmlItem->children) {
            $sorting = 1;
            foreach ($xmlItem->children->descriptor as $child){
                self::insert_descriptor($child, $descriptor->id, $sorting);
                $sorting++;
            }
        }
        
        return $descriptor;
    }
    
    private static function insert_crosssubject($xmlItem) {
        global $DB;
        
        $crosssubject = self::parse_xml_item($xmlItem);
        
        self::insert_or_update_item(DB_CROSSSUBJECTS, $crosssubject);
        self::kompetenzraster_mark_item_used(DB_CROSSSUBJECTS, $crosssubject);

        //crosssubject in DB
        //insert descriptors
        
        if ($xmlItem->descriptors) {
            if ($crosssubject->source == self::$import_source_global_id)
                $DB->delete_records(DB_DESCCROSS,array("crosssubjid"=>$crosssubject->id));

            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    $DB->insert_record(DB_DESCCROSS, array("crosssubjid"=>$crosssubject->id,"descrid"=>$descriptorid));
                }
            }
        }
        
        return $crosssubject;
    }
        
    private static function insert_taxonomy($xmlItem, $parent = 0) {
        $taxonomy = self::parse_xml_item($xmlItem);
        $taxonomy->parentid = $parent;
    
        self::insert_or_update_item(DB_TAXONOMIES, $taxonomy);
        self::kompetenzraster_mark_item_used(DB_TAXONOMIES, $taxonomy);
        
        if ($xmlItem->children) {
            foreach($xmlItem->children->taxonomy as $child) {
                self::insert_taxonomy($child, $taxonomy->id);
            }
        }
        
        return $taxonomy;
    }
    
    private static function insert_topic($xmlItem, $parent = 0) {
        global $DB;

        $topic = self::parse_xml_item($xmlItem);
        $topic->parentid = $parent;
        
        self::insert_or_update_item(DB_TOPICS, $topic);
        self::kompetenzraster_mark_item_used(DB_TOPICS, $topic);
        
        if ($xmlItem->descriptors) {
            // delete all descriptor-topic associations for this source and add them again
            // TODO: currently never delete anything
            /*
            $DB->execute("DELETE FROM {".DB_DESCTOPICS."} WHERE topicid=? AND descrid IN (
                    SELECT id FROM {".DB_DESCRIPTORS."} WHERE source=?
                )", array($topic->id, self::$import_source_local_id));
            */
            
            $i=1;
            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    $DB->insert_record(DB_DESCTOPICS, array("topicid"=>$topic->id,"descrid"=>$descriptorid, "sorting"=>$i));
                    $i++;
                }
            }
        }
    
        if ($xmlItem->children) {
            foreach($xmlItem->children->topic as $child) {
                self::insert_topic($child, $topic->id);
            }
        }
    
        return $topic;
    }
    private static function insert_subject($xmlItem) {
        $subject = self::parse_xml_item($xmlItem);

        if ($xmlItem->categoryid) {
            $subject->catid = self::get_database_id($xmlItem->categoryid);
        }
    
        self::insert_or_update_item(DB_SUBJECTS, $subject);
        self::kompetenzraster_mark_item_used(DB_SUBJECTS, $subject);
        
        return $subject;
    }
    private static function insert_schooltype($xmlItem) {
        $schooltype = self::parse_xml_item($xmlItem);

        self::insert_or_update_item(DB_SCHOOLTYPES, $schooltype);
        self::kompetenzraster_mark_item_used(DB_SCHOOLTYPES, $schooltype);
        
        return $schooltype;
    }
    private static function insert_edulevel($xmlItem) {
        $edulevel = self::parse_xml_item($xmlItem);
    
        self::insert_or_update_item(DB_EDULEVELS, $edulevel);
        self::kompetenzraster_mark_item_used(DB_EDULEVELS, $edulevel);
        
        return $edulevel;
    }
    
    private static function insert_skill($xmlItem) {
        $skill = self::parse_xml_item($xmlItem);
        
        self::insert_or_update_item(DB_SKILLS, $skill);
        self::kompetenzraster_mark_item_used(DB_SKILLS, $skill);

        return $skill;
    }
    
    
    
    
    
    
    
    private static function parse_xml_item($xml) {
        $item = simpleXMLElementToArray($xml);
        if (isset($item['@attributes'])) {
            $item = $item['@attributes'] + $item;
            unset($item['@attributes']);
        }
        
        $item = (object)$item;

        if (!isset($item->id)) {
            print_error('parse_xml_item: no id');
        }
        
        // foreign source to local source
        if (empty($item->source)) {
            // default to file source
            $item->source = self::$import_source_local_id;
        } elseif ($item->source === self::get_my_source()) {
            // source is own moodle, eg. export and import in same moodle, set it to specific
            $item->source = IMPORT_SOURCE_SPECIFIC;
        } else {
            // load local source id
            $item->source = self::add_source_if_not_exists($item->source);
        }
        
        // put sourceid and source on top of object properties, easier to read :)
        $item = (object)(array('sourceid' => $item->id, 'source' => $item->source) + (array)$item);
        unset($item->id);
        
        /*
        echo 'item: ';
        var_dump($item);
        */
        
        return $item;
    }

    private static function get_database_id(SimpleXMLElement $element) {
        global $DB;
        
        $tableMapping = array(
            'taxonomyid' => DB_TAXONOMIES,
            'exampleid' => DB_EXAMPLES,
            'descriptorid' => DB_DESCRIPTORS,
            'categoryid' => DB_CATEGORIES,
            'niveauid' => DB_NIVEAUS,
            'skillid' => DB_SKILLS,
        );
        
        if (isset($tableMapping[$element->getName()])) {
            $table = $tableMapping[$element->getName()];
        } else {
            print_error('get_database_id: wrong element name: '.$element->getName().' '.print_r($element, true));
        }
        
        $item = self::parse_xml_item($element);
        
        return $DB->get_field($table, "id", array("sourceid" => $item->sourceid, "source" => $item->source));
    }
    
    

    
    
    
    
    
    
    
    
    
    

    /*
    private static $kompetenzraster_data_ids = array();
    private static $kompetenzraster_unused_data_ids = array();
    
    private static function kompetenzraster_load_current_data_for_source() {
        global $DB;
        
        foreach (self::$sourceTables as $table) {
            self::$kompetenzraster_data_ids[$table] = $DB->get_records_menu($table, array('source'=>self::$import_source_local_id), null, 'id, sourceid AS tmp');
        }
        
        self::$kompetenzraster_unused_data_ids = self::$kompetenzraster_data_ids;
    }
    
    private static function kompetenzraster_clean_unused_data_from_source() {
        global $DB;
        
        echo "unused data: ".print_r(self::$kompetenzraster_unused_data_ids, true);
        foreach (self::$kompetenzraster_unused_data_ids as $table => $ids) {
            foreach ($ids as $localid => $sourceid) {
                // echo "delete old entry in table $table, local_id $localid, global_id $sourceid";
                // TODO: derzeit deaktiviert, es soll nichts gelöscht werden
                // $DB->delete_records($table, array("id"=>$localid));
            }
        }
    }
    
    private static function kompetenzraster_mark_item_used($table, $item) {
        if ($item->source != self::$import_source_local_id) {
            // not my source
            return;
        }
        
        if (!isset(self::$kompetenzraster_unused_data_ids[$table])) {
            print_error("unused data for table $table not found");
        }
        
        // mark used
        unset(self::$kompetenzraster_unused_data_ids[$table][$item->id]);
    }
    */

    private static function kompetenzraster_mark_item_used($table, $item) {
        // deactivated for now
    }



    
    
    /* this function deletes all categories if there are no subcategories
     i.e. if there are no topics to a subject, the subject can be deleted*/
    private static function deleteIfNoSubcategories($parenttable,$subtable,$subforeignfield,$source,$use_source_in_subtable=1,$pidlist="") {
        global $DB;
        $wherepid="";
        if ($use_source_in_subtable==1) $wheresource="source"; //zb source=1
        else $wheresource=$source; //zb 1=1
        if ($pidlist!="" AND $pidlist!="0") {
            $wherepid="AND (parentid NOT IN (".$pidlist.") OR parentid IS NULL)";
        }
        $sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? '.$wherepid.' AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE '.$wheresource.'=?)';
    
        $todeletes = $DB->get_records_sql($sql,array($source,$source));
        foreach ($todeletes as $todelete) {
            $DB->delete_records($parenttable, array("id" => $todelete->id));
        }
    }
    private static function delete_unused_descriptors($source, $crdate, $topiclist){
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
        $check = block_exacomp_data::has_data();
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