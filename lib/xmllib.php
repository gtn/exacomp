<?php

use tool_templatelibrary\api;

require_once __DIR__.'/exabis_special_id_generator.php';

class block_exacomp_SimpleXMLElement extends SimpleXMLElement {
    /**
     * Adds a child with $value inside CDATA
     * @param unknown $name
     * @param unknown $value
     */
    public function addChildWithCDATA($name, $value = NULL) {
        $new_child = $this->addChild($name);

        if ($new_child !== NULL) {
            $node = dom_import_simplexml($new_child);
            $no   = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }

        return $new_child;
    }

    public static function create($rootElement) {
        return new self('<?xml version="1.0" encoding="UTF-8"?><'.$rootElement.' />');
    }
    
    public function addChildWithCDATAIfValue($name, $value = NULL) {
        if ($value) {
            return $this->addChildWithCDATA($name, $value);
        } else {
            return $this->addChild($name, $value);
        }
    }
    
    public function addChild($name, $value = null, $namespace = null) {
        if ($name instanceof SimpleXMLElement) {
            $newNode = $name;
            $node = dom_import_simplexml($this);
            $newNode = $node->ownerDocument->importNode(dom_import_simplexml($newNode), true);
            $node->appendChild($newNode);

            // return last children, this is the added child!
            $children = $this->children();
            return $children[count($children)-1];
        } else {
            return parent::addChild($name, $value, $namespace);
        }
    }
    
    public function asPrettyXML() {
        $dom = dom_import_simplexml($this)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
    
}

class block_exacomp_ZipArchive extends ZipArchive {
    /**
     * @return block_exacomp_ZipArchive
     */
    public static function create_temp_file() {
        global $CFG;

        $file = tempnam($CFG->tempdir, "zip");
        $zip = new block_exacomp_ZipArchive();
        $zip->open($file, ZipArchive::OVERWRITE);
        
        return $zip;
    }
}

class block_exacomp_data {

    protected static $sourceTables = array(block_exacomp::DB_SKILLS, block_exacomp::DB_NIVEAUS, block_exacomp::DB_TAXONOMIES, block_exacomp::DB_CATEGORIES, block_exacomp::DB_EXAMPLES,
                    block_exacomp::DB_DESCRIPTORS, block_exacomp::DB_CROSSSUBJECTS, block_exacomp::DB_EDULEVELS, block_exacomp::DB_SCHOOLTYPES, block_exacomp::DB_SUBJECTS,
                    block_exacomp::DB_TOPICS);
    
    protected static function get_my_source() {
        return get_config('exacomp', 'mysource');
    }
    
    public static function generate_my_source() {
        $id = get_config('exacomp', 'mysource');
        
        if (!$id || !exabis_special_id_generator::validate_id($id)) {
            die('generate');
            set_config('mysource', exabis_special_id_generator::generate_random_id('EXACOMP'), 'exacomp');
        }
    }
    
    
    
    
    
    private static $sources = null; // array(local_id => global_id)
    const MIN_SOURCE_ID = 101;
    
    protected static function get_source_global_id($source_local_id) {
        self::load_sources();

        return isset(self::$sources[$source_local_id]) ? self::$sources[$source_local_id] : null;
    }
    
    protected static function get_source_from_global_id($global_id) {
        global $DB;
        
        self::load_sources();
        
        if (!$source_local_id = array_search($global_id, self::$sources)) {
            return null;
        }
        
        return $DB->get_record(block_exacomp::DB_DATASOURCES, array('id' => $source_local_id));
    }
    
    protected static function add_source_if_not_exists($source_global_id) {
        self::load_sources();
        
        if ($source_local_id = array_search($source_global_id, self::$sources)) {
            return $source_local_id;
        }
        
        global $DB;
        
        $maxId = self::$sources ? max(array_keys(self::$sources)) : 0;
        $source_local_id = max($maxId + 1, self::MIN_SOURCE_ID);

        // add new source
        $DB->execute("INSERT INTO {".block_exacomp::DB_DATASOURCES."} (id, source) VALUES (?, ?)", array($source_local_id, $source_global_id));
        
        self::$sources[$source_local_id] = $source_global_id;
        
        return $source_local_id;
    }
    
    private static function load_sources() {
        global $DB;
        
        if (self::$sources === null) {
            self::$sources = $DB->get_records_sql_menu("
                SELECT id, source AS global_id
                FROM {".block_exacomp::DB_DATASOURCES."}
            ");
        }
        
        return self::$sources;
    }

    /**
     * checks if data is imported
     */
    public static function has_data() {
        global $DB;
        
        return (bool)$DB->get_records_select('block_exacompdescriptors', 'source!='.block_exacomp::EXAMPLE_SOURCE_TEACHER, array(), null, 'id', 0, 1);
    }
    /*
     * check if there is still data in the old source format
     */
    public static function has_old_data($source) {
        global $DB;
        
        return (bool)$DB->get_records('block_exacompdescriptors', array("source" => $source), null, 'id', 0, 1);
    }
    public static function get_all_used_sources() {
        global $DB;
        
        $sources = $DB->get_records_sql("
            SELECT * FROM {".block_exacomp::DB_DATASOURCES."}
            ORDER BY NAME
        ");
        
        return $sources;
    }
    
    protected static function move_items_to_source($oldSource, $newSource) {
        global $DB;
        
        foreach (self::$sourceTables as $table) {
            $DB->execute("UPDATE {{$table}} SET source=? WHERE source=?", array($newSource, $oldSource));
        }
    }
    
    public static function delete_source($source) {
        global $DB;
        
        self::delete_mm_records($source);
        
        foreach (self::$sourceTables as $table) {
            self::truncate_table($source, $table);
        }
        
        $DB->delete_records(block_exacomp::DB_DATASOURCES, array('id' => $source));
        
        self::normalize_database();

        return true;
    }
    
    /*
     * deletes all mm records for this source
     */
    protected static function delete_mm_records($source) {
        global $DB;
        
        $tables = array(
            array(
                'table' => block_exacomp::DB_DESCTOPICS,
                'mm1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'mm2' => array('topicid', block_exacomp::DB_TOPICS),
            ),
            array(
                'table' => block_exacomp::DB_DESCEXAMP,
                'mm1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'mm2' => array('exampid', block_exacomp::DB_EXAMPLES),
            ),
            array(
                'table' => block_exacomp::DB_DESCCROSS,
                'mm1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'mm2' => array('crosssubjid', block_exacomp::DB_CROSSSUBJECTS),
            ),
            array(
                'table' => block_exacomp::DB_EXAMPTAX,
                'mm1' => array('exampleid', block_exacomp::DB_EXAMPLES),
                'mm2' => array('taxid', block_exacomp::DB_TAXONOMIES),
            ),
        );
        
        foreach ($tables as $table) {
            $DB->execute("DELETE FROM {{$table['table']}}
                WHERE 
                {$table['mm1'][0]} IN (SELECT id FROM {{$table['mm1'][1]}} WHERE source=?) AND
                {$table['mm2'][0]} IN (SELECT id FROM {{$table['mm2'][1]}} WHERE source=?)
            ", array($source, $source));
        }
    }
    
    protected static function truncate_table($source, $table) {
        global $DB;
        $DB->delete_records($table, array("source" => $source));
    }
    /*
    public static function delete_custom_competencies() {
        global $DB;
        
        // TODO: geht so nicht mehr
        $DB->delete_records(block_exacomp::DB_SUBJECTS,array('source' => block_exacomp::IMPORT_SOURCE_SPECIFIC));
        $DB->delete_records(block_exacomp::DB_TOPICS,array('source' => block_exacomp::IMPORT_SOURCE_SPECIFIC));
        $DB->delete_records(block_exacomp::DB_DESCRIPTORS,array('source' => block_exacomp::IMPORT_SOURCE_SPECIFIC));
        $examples = $DB->get_records(block_exacomp::DB_EXAMPLES,array('source' => block_exacomp::IMPORT_SOURCE_SPECIFIC));
        foreach($examples as $example) 
            block_exacomp_delete_custom_example($example->id);
        
        return true;
    }
    */
    
    public static function normalize_database() {
        global $DB;

        // delete entries with no source anymore
        foreach (self::$sourceTables as $table) {
            $sql = "DELETE FROM {{$table}} 
                        WHERE source >= ".block_exacomp_data::MIN_SOURCE_ID."
                        AND source NOT IN (SELECT id FROM {".block_exacomp::DB_DATASOURCES."})
                    ";
            $DB->execute($sql);
        }
        
        // delete unused mms 
        $tables = array(
            array(
                'table' => block_exacomp::DB_DESCTOPICS,
                'needed1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'needed2' => array('topicid', block_exacomp::DB_TOPICS),
            ),
            array(
                'table' => block_exacomp::DB_DESCEXAMP,
                'needed1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'needed2' => array('exampid', block_exacomp::DB_EXAMPLES),
            ),
            array(
                'table' => block_exacomp::DB_DESCCROSS,
                'needed1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'needed2' => array('crosssubjid', block_exacomp::DB_CROSSSUBJECTS),
            ),
            array(
                'table' => block_exacomp::DB_EXAMPTAX,
                'needed1' => array('exampleid', block_exacomp::DB_EXAMPLES),
                'needed2' => array('taxid', block_exacomp::DB_TAXONOMIES),
            ),
            array(
                'table' => block_exacomp::DB_EXAMPVISIBILITY,
                'needed1' => array('exampleid', block_exacomp::DB_EXAMPLES),
                // course / studentid exclusive!
            ),
            array(
                'table' => block_exacomp::DB_DESCVISIBILITY,
                'needed1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                // course / studentid exclusive!
            ),
            array(
                'table' => block_exacomp::DB_DESCCAT,
                'needed1' => array('descrid', block_exacomp::DB_DESCRIPTORS),
                'needed2' => array('catid', block_exacomp::DB_CATEGORIES),
            ),
            array(
                'table' => block_exacomp::DB_COURSETOPICS,
                'needed1' => array('topicid', block_exacomp::DB_TOPICS),
                'needed2' => array('courseid', "course"),
            ),
            // after examples and examptax, delete unused DB_TAXONOMIES
            array(
                'table' => block_exacomp::DB_TAXONOMIES,
                'needed1' => array('id', 'SELECT taxid FROM {'.block_exacomp::DB_EXAMPTAX.'}'),
            ),
        );
        
        $make_select = function($select) {
            if (strpos($select, ' ')) {
                return $select;
            } else {
                // is a table name
                return "SELECT id FROM {{$select}}";
            }
        };
        foreach ($tables as $table) {
            $sql = "DELETE FROM {{$table['table']}} WHERE 1!=1";
            if (!empty($table['needed1'])) {
                $sql .= " OR {$table['needed1'][0]} NOT IN ({$make_select($table['needed1'][1])})";
            }
            if (!empty($table['needed2'])) {
                $sql .= " OR {$table['needed2'][0]} NOT IN ({$make_select($table['needed2'][1])})";
            }
            if (!empty($table['needed3'])) {
                $sql .= " OR {$table['needed3'][0]} NOT IN ({$make_select($table['needed3'][1])})";
            }
            echo $sql;
            $DB->execute($sql);
        }

        // add subdescriptors to topics
        $sql = "
            INSERT INTO {".block_exacomp::DB_DESCTOPICS."}
            (topicid, descrid)
            SELECT dt_parent.topicid, d.id
            FROM {".block_exacomp::DB_DESCRIPTORS."} d
            JOIN {".block_exacomp::DB_DESCTOPICS."} dt_parent ON dt_parent.descrid=d.parentid
            LEFT JOIN {".block_exacomp::DB_DESCTOPICS."} dt ON dt.descrid=d.id
            WHERE dt.id IS NULL -- only for those, who have no topic yet
        ";
        $DB->execute($sql);
        
        // after topics, descriptors and their mm are imported
        // check if new descriptors should be visible in the courses
        // 1. descriptors directly under the topic
        $sql = "
            INSERT INTO {".block_exacomp::DB_DESCVISIBILITY."}
            (courseid, descrid, studentid, visible)
            SELECT ct.courseid, dt.descrid, 0, 1
            FROM {".block_exacomp::DB_COURSETOPICS."} ct
            JOIN {".block_exacomp::DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
            LEFT JOIN {".block_exacomp::DB_DESCVISIBILITY."} dv ON dv.descrid=dt.descrid AND dv.studentid=0
            WHERE dv.id IS NULL -- only for those, who have no visibility yet
        ";
        $DB->execute($sql);
        
        // 2. cross course descriptors used in crosssubjects
        $sql = "
            INSERT INTO {".block_exacomp::DB_DESCVISIBILITY."}
            (courseid, descrid, studentid, visible)
            SELECT cs.courseid, dc.descrid, 0, 1
            FROM {".block_exacomp::DB_CROSSSUBJECTS."} cs
            JOIN {".block_exacomp::DB_DESCCROSS."} dc ON cs.id = dc.crosssubjid
            LEFT JOIN {".block_exacomp::DB_DESCVISIBILITY."} dv ON dv.descrid=dc.descrid AND dv.studentid=0
            WHERE dv.id IS NULL AND cs.courseid != 0  -- only for those, who have no visibility yet
        ";
        $DB->execute($sql); //only necessary if we save courseinformation as well -> existing crosssubjects imported  only as drafts -> not needed
        
        //example visibility
        $sql = "
            INSERT INTO {".block_exacomp::DB_EXAMPVISIBILITY."}
            (courseid, exampleid, studentid, visible)
            SELECT DISTINCT ct.courseid, dc.exampid, 0, 1
            FROM {".block_exacomp::DB_COURSETOPICS."} ct
            JOIN {".block_exacomp::DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
            JOIN {".block_exacomp::DB_DESCVISIBILITY."} dv ON dv.descrid=dt.descrid AND dv.studentid=0
            JOIN {".block_exacomp::DB_DESCEXAMP."} dc ON dc.descrid=dt.descrid
            LEFT JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} ev ON ev.exampleid=dc.exampid AND ev.studentid=0 AND ev.courseid=ct.courseid
            WHERE ev.id IS NULL -- only for those, who have no visibility yet
        ";
        $DB->execute($sql);
        
        //example visibility： crosssubjects
        $sql = "
            INSERT INTO {".block_exacomp::DB_EXAMPVISIBILITY."}
            (courseid, exampleid, studentid, visible)
            SELECT DISTINCT cs.courseid, de.exampid, 0, 1
            FROM {".block_exacomp::DB_CROSSSUBJECTS."} cs
            JOIN {".block_exacomp::DB_DESCCROSS."} dc ON cs.id = dc.crosssubjid
            JOIN {".block_exacomp::DB_DESCVISIBILITY."} dv ON dv.descrid=dc.descrid AND dv.studentid=0
            JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.descrid=dv.descrid
            LEFT JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} ev ON ev.exampleid=de.exampid AND ev.studentid=0 AND ev.courseid=cs.courseid
            WHERE ev.id IS NULL AND cs.courseid != 0  -- only for those, who have no visibility yet
        ";
        $DB->execute($sql); //only necessary if we save courseinformation as well -> existing crosssubjects imported  only as drafts -> not needed
    }
}

class block_exacomp_data_exporter extends block_exacomp_data {
    
    static $xml;
    static $zip;
    static $filter_descriptors;
    
    public static function do_export($filter_descriptors = null) {
        global $DB, $SITE;
        
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);
        
        if (!self::get_my_source()) {
            // this can't happen anymore, because a source is automatically generated
            throw new block_exacomp_exception('source not configured, go to block settings');
            // '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingexacomp">settings</a>'
        }
        
        $xml = new block_exacomp_SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<exacomp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd" />'
        );
        
        $xml['version'] = '2015081400';
        $xml['date'] = date('c');
        $xml['source'] = self::get_my_source();
        $xml['sourcename'] = $SITE->fullname;
        
        $zip = block_exacomp_ZipArchive::create_temp_file();
        $zip->addEmptyDir('files');
        
        self::$xml = $xml;
        self::$zip = $zip;
        self::$filter_descriptors = $filter_descriptors;

        self::export_skills($xml);
        self::export_niveaus($xml);
        self::export_taxonomies($xml);
        // TODO: export categoriesn
        self::export_examples($xml);
        self::export_descriptors($xml);
        self::export_crosssubjects($xml);
        self::export_edulevels($xml);
        self::export_sources($xml);

        $zipfile = $zip->filename;
        
        if (optional_param('as_text', false, PARAM_INT)) {
            echo 'zip file size: '.filesize($zipfile)."\n\n\n";
            $zip->close();
            unlink($zipfile);
            
            echo $xml->asPrettyXML();
            
            exit;
        }
        
        $zip->addFromString('data.xml', $xml->asPrettyXML());
        $zip->close();
        
        $filename = 'exacomp-'.strftime('%Y-%m-%d %H%M').'.zip';
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipfile));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        readfile($zipfile);

        unlink($zipfile);
        
        exit;
    }
    
    private static function assign_source($xmlItem, $dbItem) {
        if ($dbItem->source && $dbItem->sourceid) {
            if ($dbItem->source == block_exacomp::IMPORT_SOURCE_DEFAULT) {
                // source und sourceid vorhanden -> von wo anders erhalten
                print_error('database error, has default source #69fvk3');
            } elseif ($dbItem->source == block_exacomp::IMPORT_SOURCE_SPECIFIC) {
                // local source -> von dieser moodle instanz selbst
                print_error('database error, has specific source #yt8d21');
            } elseif ($source = self::get_source_global_id($dbItem->source)) {
                $xmlItem['source'] = $source;
                $xmlItem['id'] = $dbItem->sourceid;
            } else {
                print_error('database error, unknown source '.$dbItem->source.' #f9ssaa8');
            }
        } else {
            // local source -> set new id
            $xmlItem['source'] = self::get_my_source();
            $xmlItem['id'] = $dbItem->id;
        }
    }
    
    private static function add_child_with_source($xmlItem, $childName, $table, $id) {
        global $DB;
        
        if ($dbItem = $DB->get_record($table, array("id" => $id))) {
            self::assign_source($xmlItem->addChild($childName), $dbItem);
        }
    }
    
    private static function export_file(block_exacomp_SimpleXMLElement $xmlItem, stored_file $file) {
        // add file to zip
        
        // testing for big archive with lots of files
        // $contenthash = md5(microtime());
        $contenthash = $file->get_contenthash();

        static $filesAdded = array();
        if (isset($filesAdded[$contenthash])) {
            // already added
            $filepath = $filesAdded[$contenthash];
        } else {
            $filepath = 'files/'.$contenthash;
            if (preg_match("!\.([^\.]+)$!", $file->get_filename(), $matches)) {
                // get extension
                $filepath .= '.'.$matches[1];
            }
            
            // mark added
            $filesAdded[$contenthash] = $filepath;
            
            // add
            self::$zip->addFromString($filepath, $file->get_content());
        }
        
        
        // data for xml item
        $xmlItem->filepath = $filepath;
        $xmlItem->filename = $file->get_filename();
        $xmlItem->mimetype = $file->get_mimetype();
        $xmlItem->author = $file->get_author();
        $xmlItem->license = $file->get_license();
        $xmlItem->timecreated = $file->get_timecreated();
        $xmlItem->timemodified = $file->get_timemodified();
    }
    
    private static function export_skills($xmlParent) {
        global $DB;
        
        $dbItems = $DB->get_records(block_exacomp::DB_SKILLS); // , array("source"=>self::$source));
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild('skills');
        
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('skill');
            self::assign_source($xmlItem, $dbItem);
            $xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
            $xmlItem->sorting = $dbItem->sorting;
        }
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
        $dbItems = $DB->get_records(block_exacomp::DB_NIVEAUS, array('parentid'=>$parentid)); // , array("source"=>self::$source));
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'niveaus');
        
        // var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('niveau');
            self::assign_source($xmlItem, $dbItem);
            $xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
            $xmlItem->sorting = $dbItem->sorting;
            
            // children
            self::export_niveaus($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_taxonomies($xmlParent, $parentid = 0) {
        global $DB;
        
        $dbItems = $DB->get_records(block_exacomp::DB_TAXONOMIES, array('parentid'=>$parentid)); // , array("source"=>self::$source));
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'taxonomies');
        
        // var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('taxonomy');
            self::assign_source($xmlItem, $dbItem);
            $xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
            $xmlItem->sorting = $dbItem->sorting;
            
            // children
            self::export_taxonomies($xmlItem, $dbItem->id);
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
        if (!$parentid && self::$filter_descriptors) {
            $filter = "
                AND e.id IN (
                    SELECT de.exampid
                    FROM {".block_exacomp::DB_DESCEXAMP."} de
                    WHERE de.descrid IN (".join(',', self::$filter_descriptors).")
                )
            ";
        } else {
            $filter = "";
        }
        
        $dbItems = $DB->get_records_sql("
            SELECT e.*
            FROM {".block_exacomp::DB_EXAMPLES."} e
            WHERE (e.source IS NULL OR e.source != ".block_exacomp::EXAMPLE_SOURCE_USER.") AND
            ".($parentid ? "e.parentid = $parentid" : "(e.parentid=0 OR e.parentid IS NULL)")."
            $filter
        ");
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'examples');

        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('example');
            self::assign_source($xmlItem, $dbItem);
            $xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
            $xmlItem->addChildWithCDATAIfValue('titleshort', $dbItem->titleshort);
            $xmlItem->addChildWithCDATAIfValue('description', $dbItem->description);
            $xmlItem->sorting = $dbItem->sorting;
            $xmlItem->timeframe = $dbItem->timeframe;
            
            if ($file = block_exacomp_get_file($dbItem, 'example_task')) {
                self::export_file($xmlItem->addChild('filetask'), $file);
            } else {
                $xmlItem->addChildWithCDATAIfValue('task', $dbItem->task);
            }
            if ($file = block_exacomp_get_file($dbItem, 'example_solution')) {
                self::export_file($xmlItem->addChild('filesolution'), $file);
            } else {
                $xmlItem->addChildWithCDATAIfValue('solution', $dbItem->solution);
            }
            
            // get solution file
            
            $xmlItem->addChildWithCDATAIfValue('completefile', $dbItem->completefile);
            $xmlItem->epop = $dbItem->epop;
            
            $xmlItem->addChildWithCDATAIfValue('metalink', $dbItem->metalink);
            $xmlItem->addChildWithCDATAIfValue('packagelink', $dbItem->packagelink);
            $xmlItem->addChildWithCDATAIfValue('restorelink', $dbItem->restorelink);
            
            $xmlItem->addChildWithCDATAIfValue('externalurl', $dbItem->externalurl);
            $xmlItem->addChildWithCDATAIfValue('externaltask', $dbItem->externaltask);
            $xmlItem->addChildWithCDATAIfValue('externalsolution', $dbItem->externalsolution);
            $xmlItem->addChildWithCDATAIfValue('tips', $dbItem->tips);
            
            
            $descriptors = $DB->get_records_sql("
                SELECT DISTINCT d.id, d.source, d.sourceid
                FROM {".block_exacomp::DB_DESCRIPTORS."} d
                JOIN {".block_exacomp::DB_DESCEXAMP."} de ON d.id = de.descrid
                WHERE de.exampid = ?
            ", array($dbItem->id));
            
            if ($descriptors) {
                $xmlItem->addChild('descriptors');
                foreach ($descriptors as $descriptor) {
                    $xmlDescripor = $xmlItem->descriptors->addChild('descriptorid');
                    self::assign_source($xmlDescripor, $descriptor);
                }
            }

            $taxonomies = block_exacomp_get_taxonomies_by_example($dbItem);
            
            if ($taxonomies) {
                $xmlItem->addChild('taxonomies');
                foreach ($taxonomies as $taxonomy) {
                    $xmlTaxonomy = $xmlItem->taxonomies->addChild('taxonomyid');
                    self::assign_source($xmlTaxonomy, $taxonomy);
                }
            }

            // children
            self::export_examples($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_descriptors($xmlParent, $parentid = 0) {
        global $DB;
        
        
        if (!$parentid && self::$filter_descriptors) {
            $dbItems = $DB->get_records_sql("
                SELECT d.*
                FROM {".block_exacomp::DB_DESCRIPTORS."} d
                WHERE parentid=0 AND d.id IN (".join(',', self::$filter_descriptors).")
            ");
        } else {
            $dbItems = $DB->get_records(block_exacomp::DB_DESCRIPTORS, array('parentid'=>$parentid));
        }
        
        if (!$dbItems) return;
        
        $xmlItems = $xmlParent->addChild($parentid ? 'children' : 'descriptors');
        //var_dump($dbItems);
        foreach ($dbItems as $dbItem) {
            $xmlItem = $xmlItems->addChild('descriptor');
            self::assign_source($xmlItem, $dbItem);
            
            self::add_child_with_source($xmlItem, 'skillid', block_exacomp::DB_SKILLS, $dbItem->skillid);
            self::add_child_with_source($xmlItem, 'niveauid', block_exacomp::DB_NIVEAUS, $dbItem->niveauid);
            
            $xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
            $xmlItem->sorting = $dbItem->sorting;
            $xmlItem->profoundness = $dbItem->profoundness;
            $xmlItem->epop = $dbItem->epop;

            // children
            self::export_descriptors($xmlItem, $dbItem->id);
        }
    }
    
    private static function export_edulevels($xmlParent, $parentid = 0) {
        $dbEdulevels = block_exacomp_get_edulevels();
        $xmlParent->addChild('edulevels');

        foreach ($dbEdulevels as $dbEdulevel) {
            $xmlEdulevel = block_exacomp_SimpleXMLElement::create('edulevel');
            self::assign_source($xmlEdulevel, $dbEdulevel);
            
            $xmlEdulevel->addChildWithCDATAIfValue('title', $dbEdulevel->title);
            
            self::export_schooltypes($xmlEdulevel, $dbEdulevel);
            
            if (!empty($xmlEdulevel->schooltypes)) {
                $xmlParent->edulevels->addChild($xmlEdulevel);
            }
        }
    }
    
    private static function export_crosssubjects($xmlParent, $parentid = 0) {
        global $DB;
        
        $dbCrosssubjects = block_exacomp_get_crosssubjects();
        $xmlParent->addChild('crosssubjects');

        foreach ($dbCrosssubjects as $dbCrosssubject) {
            $xmlCrosssubject = $xmlParent->crosssubjects->addchild('crosssubject');
            self::assign_source($xmlCrosssubject, $dbCrosssubject);
            
            $xmlCrosssubject->addChildWithCDATAIfValue('title', $dbCrosssubject->title);
            $xmlCrosssubject->addChildWithCDATAIfValue('description', $dbCrosssubject->description);
            
            $subject = $DB->get_record(block_exacomp::DB_SUBJECTS, array('id'=>$dbCrosssubject->subjectid));
            
            if($subject){
            	$xmlSubject = $xmlCrosssubject->addChild('subjectid');
            	self::assign_source($xmlSubject, $subject);
            }

            $xmlCrosssubject->courseid = $dbCrosssubject->courseid;
            
                 $descriptors = $DB->get_records_sql("
                SELECT DISTINCT d.id, d.source, d.sourceid
                FROM {".block_exacomp::DB_DESCRIPTORS."} d
                JOIN {".block_exacomp::DB_DESCCROSS."} dc ON d.id = dc.descrid
                WHERE dc.crosssubjid = ?
            ", array($dbCrosssubject->id));
                 
            if ($descriptors) {
                $xmlDescriptors = $xmlCrosssubject->addChild('descriptors');
                foreach ($descriptors as $descriptor) {
                    $xmlDescripor = $xmlDescriptors->addChild('descriptorid');
                    self::assign_source($xmlDescripor, $descriptor);
                }
            }
        }
    }
    
    private static function export_schooltypes($xmlEdulevel, $dbEdulevel) {
        $xmlEdulevel->addChild('schooltypes');
        $dbSchooltypes = block_exacomp_get_schooltypes($dbEdulevel->id);

        foreach ($dbSchooltypes as $dbSchooltype) {
            $xmlSchooltype = block_exacomp_SimpleXMLElement::create('schooltype');
            self::assign_source($xmlSchooltype, $dbSchooltype);
            
            $xmlSchooltype->addChildWithCDATAIfValue('title', $dbSchooltype->title);
            $xmlSchooltype->sorting = $dbSchooltype->sorting;
            $xmlSchooltype->isoez = $dbSchooltype->isoez;
            $xmlSchooltype->epop = $dbSchooltype->epop;
            
            self::export_subjects($xmlSchooltype, $dbSchooltype);

            if (!empty($xmlSchooltype->subjects)) {
                $xmlEdulevel->schooltypes->addChild($xmlSchooltype);
            }
        }
    }

    private static function export_subjects($xmlSchooltype, $dbSchooltype) {
        global $DB;
        
        $xmlSchooltype->addChild('subjects');
        $dbSubjects = $DB->get_records(block_exacomp::DB_SUBJECTS, array('stid' => $dbSchooltype->id));
        foreach($dbSubjects as $dbSubject){
            $xmlSubject = block_exacomp_SimpleXMLElement::create('subject');
            self::assign_source($xmlSubject, $dbSubject);
            
            $xmlSubject->addChildWithCDATAIfValue('title', $dbSubject->title);
            $xmlSubject->addChildWithCDATAIfValue('titleshort', $dbSubject->titleshort);
            $xmlSubject->addChildWithCDATAIfValue('infolink', $dbSubject->infolink);
            $xmlSubject->sorting = $dbSubject->sorting;
            $xmlSubject->epop = $dbSubject->epop;
            
            self::export_topics($xmlSubject, $dbSubject);
        
            if (!empty($xmlSubject->topics)) {
                $xmlSchooltype->subjects->addChild($xmlSubject);
            }
        }
    }
    
    private static function export_topics($xmlSubject, $dbSubject) {
        global $DB;
        
        $xmlSubject->addChild('topics');
        if (self::$filter_descriptors) {
            $dbTopics = $DB->get_records_sql("
                SELECT t.*
                FROM {".block_exacomp::DB_TOPICS."} t
                WHERE t.subjid = ? AND
                    t.id IN (
                    SELECT dt.topicid
                    FROM {".block_exacomp::DB_DESCTOPICS."} dt
                    WHERE dt.descrid IN (".join(',', self::$filter_descriptors).")
                )
            ", array($dbSubject->id));
        } else {
            $dbTopics = $DB->get_records(block_exacomp::DB_TOPICS, array('subjid' => $dbSubject->id));
        }
        
        foreach($dbTopics as $dbTopic){
            
            $xmlTopic = $xmlSubject->topics->addChild('topic');
            self::assign_source($xmlTopic, $dbTopic);
            
            $xmlTopic->addChildWithCDATAIfValue('title', $dbTopic->title);
            $xmlTopic->addChildWithCDATAIfValue('titleshort', $dbTopic->titleshort);
            $xmlTopic->addChildWithCDATAIfValue('description', $dbTopic->description);
            $xmlTopic->sorting = $dbTopic->sorting;
            $xmlTopic->epop = $dbTopic->epop;
            $xmlTopic->numb = $dbTopic->numb;
            
            if (self::$filter_descriptors) {
                $filter = " AND d.id IN (".join(',', self::$filter_descriptors).")";
            } else {
                $filter = "";
            }
            
            $descriptors = $DB->get_records_sql("
                SELECT DISTINCT d.id, d.source, d.sourceid
                FROM {".block_exacomp::DB_DESCRIPTORS."} d
                JOIN {".block_exacomp::DB_DESCTOPICS."} dt ON d.id = dt.descrid
                WHERE dt.topicid = ?
                    $filter
            ", array($dbTopic->id));
            
            if ($descriptors) {
                $xmlDescripors = $xmlTopic->addChild('descriptors');
                foreach ($descriptors as $descriptor) {
                    $xmlDescripor = $xmlDescripors->addChild('descriptorid');
                    self::assign_source($xmlDescripor, $descriptor);
                }
            }
        }
    }

    private static function export_sources(block_exacomp_SimpleXMLElement $xmlParent) {
        global $DB;
        
        // rather then exporting all sources in the database
        // we only export the sources used in the xml
        // this helps later, when only partial exports are made.
        // eg. only one course
        
        // get sources
        $sources = array();
        // find all sources used in the xml (under /exacomp, which has a source, but we don't need it here)
        foreach ($xmlParent->xpath("/exacomp//*/@source") as $source) {
            $sources[(string)$source] = 1;
        }
        $sources = array_keys($sources);
        
        if (!$sources) return;
        
        $xmlParent->addChild('sources');

        foreach ($sources as $source) {
            if (!$source = self::get_source_from_global_id($source)) {
                continue;
            }
            
            $xmlSource = $xmlParent->sources->addchild('source');
            $xmlSource['id'] = $source->source;
            $xmlSource->name = $source->name;
        }
    }
}

class block_exacomp_data_importer extends block_exacomp_data {
    
    private static $import_source_type;
    private static $import_source_global_id;
    private static $import_source_local_id;
    
    private static $import_time = null;
    
    private static $zip;
    
    public static function do_import_string($data = null, $par_source = 1, $cron = false) {
        global $CFG;

        if (!$data) {
            throw new block_exacomp_exception('filenotfound');
        }
        
        $file = tempnam($CFG->tempdir, "zip");
        file_put_contents($file, $data);
        
        $ret = self::do_import_file($file, $par_source, $cron);
        
        @unlink($file);
        
        return $ret;
    }
    
    /**
     *
     * @param String $data xml content
     * @param int $source default is 1, for specific import 2 is used. A specific import can be done by teachers and only effects data from topic leven downwards (topics, descriptors, examples)
     * @param int $cron should always be 0, 1 if method is called by the cron job
     */
    public static function do_import_file($file = null, $par_source = 1, $cron = false) {
        global $DB, $CFG;
    
        if (!$file) {
            throw new block_exacomp_exception('filenotfound');
        }
            
        if (!file_exists($file)) {
            throw new block_exacomp_exception('filenotfound');
        }
        
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);
        
        self::$import_source_type = $par_source;
        self::$import_time = time();
        
        // guess it's a zip file
        $zip = new block_exacomp_ZipArchive();
        $ret = $zip->open($file, ZipArchive::CHECKCONS);
        
        if ($ret === true) {
            // a zip file
            self::$zip = $zip;
            
            if (!$xml = $zip->getFromName('data.xml')) {
                throw new block_exacomp_exception('wrong zip file');
            }
            
            /*
             * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
             * immediate useage
             */
            $xml = simplexml_load_string($xml,'block_exacomp_SimpleXMLElement', LIBXML_NOCDATA);

            if (!$xml) {
                throw new block_exacomp_exception('wrong zip file content');
            }
        } else {
            // on error -> try as xml

            /*
             * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
             * immediate useage
             */
            $xml = @simplexml_load_file($file,'block_exacomp_SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml) {
                throw new block_exacomp_exception('wrong file');
            }
        }
        

        if(isset($xml->table)){
            throw new block_exacomp_exception('oldxmlfile');
        }
        
        if (empty($xml['source'])) {
            throw new block_exacomp_exception('oldxmlfile');
        }
        
        self::$import_source_global_id = (string)$xml['source'];
        self::$import_source_local_id = 
            self::$import_source_global_id == self::get_my_source() ? 0 : self::add_source_if_not_exists(self::$import_source_global_id);
        
        // update scripts for new source format
        if (self::has_old_data(block_exacomp::IMPORT_SOURCE_DEFAULT)) {
            if (self::$import_source_type != block_exacomp::IMPORT_SOURCE_DEFAULT) {
                print_error('you first need to import the default sources!');
            }
            self::move_items_to_source(block_exacomp::IMPORT_SOURCE_DEFAULT, self::$import_source_local_id);
        }
        else {
            // always move old specific data
            self::move_items_to_source(block_exacomp::IMPORT_SOURCE_SPECIFIC, self::$import_source_local_id);
        }
        
        // self::kompetenzraster_load_current_data_for_source();
        self::delete_mm_records(self::$import_source_local_id);

        self::truncate_table(self::$import_source_local_id, block_exacomp::DB_SKILLS);
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
        
        self::truncate_table(self::$import_source_local_id, block_exacomp::DB_TAXONOMIES);
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
        
        if (isset($xml->descriptors)) {
            foreach($xml->descriptors->descriptor as $descriptor) {
                self::insert_descriptor($descriptor);
            }
        }
        
        if (isset($xml->examples)) {
            foreach($xml->examples->example as $example) {
                self::insert_example($example);
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
        
     	if(isset($xml->crosssubjects)) {
            //insert empty draft as first entry
            block_exacomp_init_cross_subjects();
            foreach($xml->crosssubjects->crosssubject as $crosssubject) {
                self::insert_crosssubject($crosssubject);
            }
        }
        
        if(isset($xml->sources)) {
            foreach($xml->sources->source as $source) {
                self::insert_source($source);
            }
        }
        
        // self::kompetenzraster_clean_unused_data_from_source();
        // TODO: was ist mit desccross?
        self::delete_unused_descriptors(self::$import_source_local_id, self::$import_time, implode(",", $insertedTopics));
    
        //self::deleteIfNoSubcategories("block_exacompdescrexamp_mm","block_exacompdescriptors","id",self::$import_source_local_id,1,0,"descrid");
        self::deleteIfNoSubcategories("block_exacompexamples","block_exacompdescrexamp_mm","exampid",self::$import_source_local_id,0);
        //self::deleteIfNoSubcategories("block_exacompdescrtopic_mm","block_exacompdescriptors","id",self::$import_source_local_id,1,0,"descrid");
        self::deleteIfNoSubcategories("block_exacomptopics","block_exacompdescrtopic_mm","topicid",self::$import_source_local_id,0,implode(",", $insertedTopics));
        self::deleteIfNoSubcategories("block_exacompsubjects","block_exacomptopics","subjid",self::$import_source_local_id);
        self::deleteIfNoSubcategories("block_exacompschooltypes","block_exacompsubjects","stid",self::$import_source_local_id);
        self::deleteIfNoSubcategories("block_exacompedulevels","block_exacompschooltypes","elid",self::$import_source_local_id);

        self::normalize_database();
    
        block_exacomp_settstamp();
        
        return true;
    }

    
    
    
    

    
    private static function insert_or_update_item($table, $item) {
        global $DB;
        
        $where = $item->source ? array('source' => $item->source, 'sourceid' => $item->sourceid) : array('id'=>$item->id);
        if ($dbItem = $DB->get_record($table, $where)) {
            $item->id = $dbItem->id;
            
            if ($item->source == self::$import_source_local_id) {
                // only update, if coming from same source as xml
                $DB->update_record($table, $item);
            } else {
                // source not xml source -> skip update
            }
        } else {
            $new_id = $DB->insert_record($table, $item);
            if ($item->source) {
                // foreign source
                $item->id = $new_id;
            } else {
                // move to specified id
                $DB->execute("UPDATE {".$table."} SET id=? WHERE id=?", array($item->id, $new_id));
            }
        }
    }
    
    private static function insert_source($xmlItem) {
        
        if (!$dbSource = self::get_source_from_global_id($xmlItem['id'])) {
            // only for already inserted sources, update them
            return;
        }
        
        block_exacomp_db::update_record(block_exacomp::DB_DATASOURCES, array(
            'id' => $dbSource->id,
        ), array(
            'name' => (string)$xmlItem->name
        ));
    }
    
    private static function insert_file($filearea, block_exacomp_SimpleXMLElement $xmlItem, $item) {
        if (!self::$zip) {
            return;
        }
        
        $filecontent = self::$zip->getFromName($xmlItem->filepath);

        $fs = get_file_storage();
        
        // delete old file
        $fs->delete_area_files(context_system::instance()->id, 'block_exacomp', $filearea, $item->id);
        
        // reimport
        $file = $fs->create_file_from_string(array(
            'contextid' => context_system::instance()->id,
            'component' => 'block_exacomp',
            'filearea' => $filearea,
            'itemid' => $item->id,
            'filepath' => '/',
                        
            'filename' => (string)$xmlItem->filename,
            'mimetype' => (string)$xmlItem->mimetype,
            'author' => (string)$xmlItem->author,
            'license' => (string)$xmlItem->license,
            'timecreated' => (int)$xmlItem->timecreated,
            'timemodified' => (int)$xmlItem->timemodified
        ), $filecontent);
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
        
        self::insert_or_update_item(block_exacomp::DB_NIVEAUS, $item);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_NIVEAUS, $item);
        
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
        
        self::insert_or_update_item(block_exacomp::DB_EXAMPLES, $item);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_EXAMPLES, $item);
        
        // if local example, move to source teacher
        if (!$item->source) {
            block_exacomp_db::insert_or_update_record(block_exacomp::DB_EXAMPLES, array("id"=>$item->id), array('source' => block_exacomp::EXAMPLE_SOURCE_TEACHER));
        }
        
        // has to be called after inserting the example, because the id is needed!
        if ($xmlItem->filesolution) {
            self::insert_file('example_solution', $xmlItem->filesolution, $item);
        }
        if ($xmlItem->filetask) {
            self::insert_file('example_task', $xmlItem->filetask, $item);
        }
        
        if ($xmlItem->taxonomies) {
            foreach ($xmlItem->taxonomies->taxonomyid as $taxonomy) {
                if ($taxonomyid = self::get_database_id($taxonomy)) {
                    block_exacomp_db::insert_or_update_record(block_exacomp::DB_EXAMPTAX, array("exampleid"=>$item->id, "taxid"=>$taxonomyid));
                }
            }
        }
        
        if ($xmlItem->descriptors) {
            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCEXAMP, array("exampid"=>$item->id, "descrid"=>$descriptorid));
                } else {
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
    
        self::insert_or_update_item(block_exacomp::DB_CATEGORIES, $item);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_CATEGORIES, $item);
        
        // OLD:
        /*
        if ($dbItem = $DB->get_record(block_exacomp::DB_EXAMPLES, array("sourceid"=>$item->source, 'sourceid'=>$item->sourceid))) {
            $item->id = $dbItem->id;
            $DB->update_record(block_exacomp::DB_EXAMPLES, $item);
        } elseif ($item->source == block_exacomp::IMPORT_SOURCE_SPECIFIC && $dbItem = $DB->get_record(block_exacomp::DB_EXAMPLES, array("id"=>$item->sourceid))) {
            $item->id = $dbItem->id;
            $DB->update_record(block_exacomp::DB_EXAMPLES, $item);
        } else {
            $item->id = $DB->insert_record(block_exacomp::DB_EXAMPLES, $item);
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
        if ($xmlItem->skillid)
            $descriptor->skillid = self::get_database_id($xmlItem->skillid);
        if (!isset($descriptor->profoundness))
            $descriptor->profoundness = 0;
        
        // brauchen wir nicht mehr:
        /*
        //if specific import and descriptor already normal imported -> return
        if(block_exacomp_data_importer::$import_source_type != block_exacomp::IMPORT_SOURCE_DEFAULT) {
            if($descriptorObj = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>block_exacomp::IMPORT_SOURCE_DEFAULT)))
                return;
        }
    
        //other way round: if normale import and descriptor already specific imported -> return
        if(block_exacomp_data_importer::$import_source_type == block_exacomp::IMPORT_SOURCE_DEFAULT){
            if($descriptorObj = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(), "source"=>block_exacomp::IMPORT_SOURCE_SPECIFIC)))
                return;
        }
        
        */
        

        self::insert_or_update_item(block_exacomp::DB_DESCRIPTORS, $descriptor);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_DESCRIPTORS, $descriptor);
        
        // if local descriptor, move to custom source
        if (!$descriptor->source) {
            block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCRIPTORS, array("id"=>$descriptor->id), array('source' => block_exacomp::CUSTOM_CREATED_DESCRIPTOR));
        }
        
        if ($xmlItem->examples) {
            throw new block_exacomp_exception('oldxmlfile');
        }
        
        if ($xmlItem->categories) {
            foreach ($xmlItem->categories->categoryid as $category) {
                if ($categoryid = self::get_database_id($category)) {
                    block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCCAT, array("descrid"=>$descriptor->id, "catid"=>$categoryid));
                }
            }
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
        
    	if ($xmlItem->subjectid) {
            $crosssubject->subjectid = self::get_database_id($xmlItem->subjectid);
        }
        self::insert_or_update_item(block_exacomp::DB_CROSSSUBJECTS, $crosssubject);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_CROSSSUBJECTS, $crosssubject);

        //crosssubject in DB
        //insert descriptors
        
        if ($xmlItem->descriptors) {
            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCCROSS, array("crosssubjid"=>$crosssubject->id,"descrid"=>$descriptorid));
                }
            }
        }
        
        return $crosssubject;
    }
        
    private static function insert_taxonomy($xmlItem, $parent = 0) {
        $taxonomy = self::parse_xml_item($xmlItem);
        $taxonomy->parentid = $parent;
    
        self::insert_or_update_item(block_exacomp::DB_TAXONOMIES, $taxonomy);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_TAXONOMIES, $taxonomy);
        
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
        
        self::insert_or_update_item(block_exacomp::DB_TOPICS, $topic);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_TOPICS, $topic);
        
        if ($xmlItem->descriptors) {
            $i=1;
            foreach($xmlItem->descriptors->descriptorid as $descriptor) {
                if ($descriptorid = self::get_database_id($descriptor)) {
                    block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCTOPICS, array("topicid"=>$topic->id,"descrid"=>$descriptorid), array("sorting"=>$i));
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
    
        self::insert_or_update_item(block_exacomp::DB_SUBJECTS, $subject);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_SUBJECTS, $subject);
        
        return $subject;
    }
    private static function insert_schooltype($xmlItem) {
        $schooltype = self::parse_xml_item($xmlItem);

        self::insert_or_update_item(block_exacomp::DB_SCHOOLTYPES, $schooltype);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_SCHOOLTYPES, $schooltype);
        
        return $schooltype;
    }
    private static function insert_edulevel($xmlItem) {
        $edulevel = self::parse_xml_item($xmlItem);
    
        self::insert_or_update_item(block_exacomp::DB_EDULEVELS, $edulevel);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_EDULEVELS, $edulevel);
        
        return $edulevel;
    }
    
    private static function insert_skill($xmlItem) {
        $skill = self::parse_xml_item($xmlItem);
        
        self::insert_or_update_item(block_exacomp::DB_SKILLS, $skill);
        self::kompetenzraster_mark_item_used(block_exacomp::DB_SKILLS, $skill);

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
            throw new block_exacomp_exception('wrong xml format');
        }
        
        // foreign source to local source
        if (empty($item->source)) {
            // default to file source
            $item->source = self::$import_source_local_id;
        } elseif ($item->source === self::get_my_source()) {
            // source is own moodle, eg. export and import in same moodle
            $item->source = 0;
            $item->sourceid = 0;
            // keep $item->id
            return $item;
        } else {
            // load local source id
            $item->source = self::add_source_if_not_exists($item->source);
        }
        
        // put sourceid and source on top of object properties, easier to read :)
        $item = (object)(array('sourceid' => $item->id, 'source' => $item->source) + (array)$item);
        unset($item->id);
        
        return $item;
    }

    private static function get_database_id(SimpleXMLElement $element) {
        global $DB;
        
        $tableMapping = array(
            'taxonomyid' => block_exacomp::DB_TAXONOMIES,
            'exampleid' => block_exacomp::DB_EXAMPLES,
            'descriptorid' => block_exacomp::DB_DESCRIPTORS,
            'categoryid' => block_exacomp::DB_CATEGORIES,
            'niveauid' => block_exacomp::DB_NIVEAUS,
            'skillid' => block_exacomp::DB_SKILLS,
        	'subjectid' => block_exacomp::DB_SUBJECTS
        );
        
        if (isset($tableMapping[$element->getName()])) {
            $table = $tableMapping[$element->getName()];
        } else {
            print_error('get_database_id: wrong element name: '.$element->getName().' '.print_r($element, true));
        }
        
        $item = self::parse_xml_item($element);
        
        $where = $item->source ? array('source' => $item->source, 'sourceid' => $item->sourceid) : array('id'=>$item->id);
        return $DB->get_field($table, "id", $where);
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