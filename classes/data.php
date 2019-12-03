<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
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

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../lib/exabis_special_id_generator.php';
require_once __DIR__.'/../backup/test_backup.php';
require_once __DIR__.'/../backup/test_restore.php';

use block_exacomp\globals as g;
use Super\Fs;

class ZipArchive extends \ZipArchive {
	/**
	 * @return ZipArchive
	 */
	public static function create_temp_file() {
		global $CFG;

		$file = tempnam($CFG->tempdir, "zip");
		$zip = new ZipArchive();
		$zip->open($file, ZipArchive::OVERWRITE);
		
		return $zip;
	}
}

class data {

	protected static $sourceTables = array(BLOCK_EXACOMP_DB_SKILLS, BLOCK_EXACOMP_DB_NIVEAUS, BLOCK_EXACOMP_DB_TAXONOMIES, BLOCK_EXACOMP_DB_CATEGORIES, BLOCK_EXACOMP_DB_EXAMPLES,
					BLOCK_EXACOMP_DB_DESCRIPTORS, BLOCK_EXACOMP_DB_CROSSSUBJECTS, BLOCK_EXACOMP_DB_EDULEVELS, BLOCK_EXACOMP_DB_SCHOOLTYPES, BLOCK_EXACOMP_DB_SUBJECTS,
					BLOCK_EXACOMP_DB_TOPICS);

	public static function prepare() {
		// this is a dummy to load all the other classes
	}

	public static function get_my_source() {
		return get_config('exacomp', 'mysource');
	}
	
	public static function generate_my_source() {
		$id = get_config('exacomp', 'mysource');
		
		if (!$id || !\exabis_special_id_generator::validate_id($id)) {
			set_config('mysource', \exabis_special_id_generator::generate_random_id('EXACOMP'), 'exacomp');
		}
	}
	
	
	
	
	
	private static $sources = null; // array(local_id => global_id)
	const MIN_SOURCE_ID = 101;
	
	public static function get_source_global_id($source_local_id) {
		self::load_sources();

		return isset(self::$sources[$source_local_id]) ? self::$sources[$source_local_id] : null;
	}
	
	protected static function get_source_from_global_id($global_id) {
		self::load_sources();
		
		if (!$source_local_id = array_search($global_id, self::$sources)) {
			return null;
		}
		
		return g::$DB->get_record(BLOCK_EXACOMP_DB_DATASOURCES, array('id' => $source_local_id));
	}
	
	protected static function add_source_if_not_exists($source_global_id, $schedulerId = 0) {
	    if ($schedulerId > 0) {
            g::$DB->update_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('source' => $source_global_id), array('id' => $schedulerId));
            return $schedulerId;
        }
		self::load_sources();
		if ($source_local_id = array_search($source_global_id, self::$sources)) {
			return $source_local_id;
		}
		$maxId = self::$sources ? max(array_keys(self::$sources)) : 0;
		$source_local_id = max($maxId + 1, self::MIN_SOURCE_ID);
		// add new source
        g::$DB->insert_record_raw(BLOCK_EXACOMP_DB_DATASOURCES, ['id' => intval($source_local_id), 'source' => $source_global_id], true, false, true);
		//g::$DB->execute("INSERT INTO {".BLOCK_EXACOMP_DB_DATASOURCES."} (id, source) VALUES (?, ?)", array($source_local_id, $source_global_id));

		self::$sources[$source_local_id] = $source_global_id;
		
		return $source_local_id;
	}
	
	private static function load_sources() {
		if (self::$sources === null) {
			self::$sources = g::$DB->get_records_sql_menu("
				SELECT id, source AS global_id
				FROM {".BLOCK_EXACOMP_DB_DATASOURCES."}
			");
		}
		
		return self::$sources;
	}

	/**
	 * checks if data is imported
	 */
	public static function has_data() {
		return (bool)g::$DB->get_records_select('block_exacompdescriptors', 'source!='.BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER, array(), null, 'id', 0, 1);
	}
	/*
	 * check if there is still data in the old source format
	 */
	public static function has_old_data($source) {
		return (bool)g::$DB->get_records('block_exacompdescriptors', array("source" => $source), null, 'id', 0, 1);
	}
	public static function get_all_used_sources() {
		// check if source is used in descriptor table
		$sources = g::$DB->get_records_sql("
			SELECT s.*
			FROM {".BLOCK_EXACOMP_DB_DATASOURCES."} s
			WHERE s.id IN (
				SELECT DISTINCT source FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."}
				UNION
				SELECT DISTINCT source FROM {".BLOCK_EXACOMP_DB_EXAMPLES."}
			)
			ORDER BY NAME
		");
		
		return $sources;
	}
	
	protected static function move_items_to_source($oldSource, $newSource) {
		foreach (self::$sourceTables as $table) {
			g::$DB->execute("UPDATE {{$table}} SET source=? WHERE source=?", array($newSource, $oldSource));
		}
	}
	
	public static function delete_source($source) {
		self::delete_mm_records($source);
		
		foreach (self::$sourceTables as $table) {
			self::truncate_table($source, $table);
		}
		
		g::$DB->delete_records(BLOCK_EXACOMP_DB_DATASOURCES, array('id' => $source));
		
		self::normalize_database();

		return true;
	}
	
	/*
	 * deletes all mm records for this source
	 */
	protected static function delete_mm_records($source) {
		$tables = array(
			array(
				'table' => BLOCK_EXACOMP_DB_DESCTOPICS,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('topicid', BLOCK_EXACOMP_DB_TOPICS),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCEXAMP,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('exampid', BLOCK_EXACOMP_DB_EXAMPLES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCCROSS,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('crosssubjid', BLOCK_EXACOMP_DB_CROSSSUBJECTS),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCCAT,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('catid', BLOCK_EXACOMP_DB_CATEGORIES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_EXAMPTAX,
				'mm1' => array('exampleid', BLOCK_EXACOMP_DB_EXAMPLES),
				'mm2' => array('taxid', BLOCK_EXACOMP_DB_TAXONOMIES),
			),
		);
		
		foreach ($tables as $table) {
			g::$DB->execute("DELETE FROM {{$table['table']}}
				WHERE 
				{$table['mm1'][0]} IN (SELECT id FROM {{$table['mm1'][1]}} WHERE source=?) AND
				{$table['mm2'][0]} IN (SELECT id FROM {{$table['mm2'][1]}} WHERE source=?)
			", array($source, $source));
		}
	}
	
	protected static function truncate_table($source, $table) {
		g::$DB->delete_records($table, array("source" => $source));
	}

        /*
     * public static function delete_custom_competencies() {
     * global $DB;
     *
     * // TODO: geht so nicht mehr
     * $DB->delete_records(BLOCK_EXACOMP_DB_SUBJECTS,array('source' => BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC));
     * $DB->delete_records(BLOCK_EXACOMP_DB_TOPICS,array('source' => BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC));
     * $DB->delete_records(BLOCK_EXACOMP_DB_DESCRIPTORS,array('source' => BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC));
     * $examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES,array('source' => BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC));
     * foreach($examples as $example)
     * block_exacomp_delete_custom_example($example->id);
     *
     * return true;
     * }
     */
    public static function normalize_database()
    {
        // delete entries with no source anymore
        foreach (self::$sourceTables as $table) {
            $sql = "DELETE FROM {{$table}} 
						WHERE source >= " . data::MIN_SOURCE_ID . "
						AND source NOT IN (SELECT id FROM {" . BLOCK_EXACOMP_DB_DATASOURCES . "})
					";
            g::$DB->execute($sql);
        }

        // delete unused mms
        $tables = array(
            array(
                'table' => BLOCK_EXACOMP_DB_DESCTOPICS,
                'needed1' => array(
                    'descrid',
                    BLOCK_EXACOMP_DB_DESCRIPTORS
                ),
                'needed2' => array(
                    'topicid',
                    BLOCK_EXACOMP_DB_TOPICS
                )
            ),
            array(
                'table' => BLOCK_EXACOMP_DB_DESCEXAMP,
                'needed1' => array(
                    'descrid',
                    BLOCK_EXACOMP_DB_DESCRIPTORS
                ),
                'needed2' => array(
                    'exampid',
                    BLOCK_EXACOMP_DB_EXAMPLES
                )
            ),
            array(
                'table' => BLOCK_EXACOMP_DB_DESCCROSS,
                'needed1' => array(
                    'descrid',
                    BLOCK_EXACOMP_DB_DESCRIPTORS
                ),
                'needed2' => array(
                    'crosssubjid',
                    BLOCK_EXACOMP_DB_CROSSSUBJECTS
                )
            ),
            array(
                'table' => BLOCK_EXACOMP_DB_EXAMPTAX,
                'needed1' => array(
                    'exampleid',
                    BLOCK_EXACOMP_DB_EXAMPLES
                ),
                'needed2' => array(
                    'taxid',
                    BLOCK_EXACOMP_DB_TAXONOMIES
                )
            ),
            array(
                'table' => BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
				'needed1' => array('exampleid', BLOCK_EXACOMP_DB_EXAMPLES),
				// course / studentid exclusive!
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCVISIBILITY,
				'needed1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				// course / studentid exclusive!
			),
			array(
				'table' => BLOCK_EXACOMP_DB_TOPICVISIBILITY,
				'needed1' => array('topicid', BLOCK_EXACOMP_DB_TOPICS),
				// course / studentid exclusive!
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCCAT,
				'needed1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'needed2' => array('catid', BLOCK_EXACOMP_DB_CATEGORIES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_COURSETOPICS,
				'needed1' => array('topicid', BLOCK_EXACOMP_DB_TOPICS),
				'needed2' => array('courseid', "course"),
			),
			// after examples and examptax, delete unused BLOCK_EXACOMP_DB_TAXONOMIES
			array(
				'table' => BLOCK_EXACOMP_DB_TAXONOMIES,
				'needed1' => array('id', 'SELECT taxid FROM {'.BLOCK_EXACOMP_DB_EXAMPTAX.'}'),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_NIVEAUS,
				'needed1' => array('id', 'SELECT niveauid FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'}'),
			),
						
			// delete examples without descriptors
			array(
				'table' => BLOCK_EXACOMP_DB_EXAMPLES,
				'needed1' => array('id', 'SELECT exampid FROM {'.BLOCK_EXACOMP_DB_DESCEXAMP.'}'),
			),
			// delete topics without descriptors
			// ist so nicht mehr richtig
			// eigentlich: topics loeschen, wenn das subject nicht existiert
			// subjects loeschen, wenn der schooltype nicht existiert
			/*
			array(
				'table' => BLOCK_EXACOMP_DB_TOPICS,
				'needed1' => array('id', 'SELECT topicid FROM {'.BLOCK_EXACOMP_DB_DESCTOPICS.'}'),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_SUBJECTS,
				'needed1' => array('id', 'SELECT subjid FROM {'.BLOCK_EXACOMP_DB_TOPICS.'}'),
			),
			
			array(
				'table' => BLOCK_EXACOMP_DB_CATEGORIES,
				'needed1' => array('id', 'SELECT catid FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'}'),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_SCHOOLTYPES,
				'needed1' => array('id', 'SELECT stid FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'}'),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_EDULEVELS,
				'needed1' => array('id', 'SELECT elid FROM {'.BLOCK_EXACOMP_DB_SCHOOLTYPES.'}'),
			),
			*/
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
			g::$DB->execute($sql);
		}

		// delete unused sources
		/*
		$sql = [];
		foreach (self::$sourceTables as $table) {
			$sql[] = "SELECT CONCAT(source, '{$table}') AS id, count(*) FROM {{$table}} WHERE source >= ".data::MIN_SOURCE_ID.' GROUP BY source';
		}
		$sql = join(" UNION ", $sql);
		print_r($DB->get_records_sql($sql));
		exit;
		*/
		/*
		 $sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_DATASOURCES."} WHERE id NOT IN (
		 ".join(" UNION ", $sql)."
		 )";
		 */
		// add topic visibility to course if associated
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."}
			(courseid, topicid, studentid, visible)
			SELECT ct.courseid, ct.topicid, 0, 1
			FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
			LEFT JOIN {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tv ON tv.topicid = ct.topicid
			WHERE tv.id IS NULL -- only for those, who have no visibility yet
		";
		g::$DB->execute($sql);
		
		// add subdescriptors to topics
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_DESCTOPICS."}
			(topicid, descrid)
			SELECT dt_parent.topicid, d.id
			FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt_parent ON dt_parent.descrid=d.parentid
			LEFT JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON dt.descrid=d.id
			WHERE dt.id IS NULL -- only for those, who have no topic yet
		";
		g::$DB->execute($sql);
		
		// after topics, descriptors and their mm are imported
		// check if new descriptors should be visible in the courses
		// 1. descriptors directly under the topic
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_DESCVISIBILITY."}
			(courseid, descrid, studentid, visible)
			SELECT ct.courseid, dt.descrid, 0, 1
			FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
			LEFT JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dt.descrid AND dv.studentid=0
			WHERE dv.id IS NULL -- only for those, who have no visibility yet
		";
		g::$DB->execute($sql);
		
		// 2. cross course descriptors used in crosssubjects
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_DESCVISIBILITY."}
			(courseid, descrid, studentid, visible)
			SELECT cs.courseid, dc.descrid, 0, 1
			FROM {".BLOCK_EXACOMP_DB_CROSSSUBJECTS."} cs
			JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON cs.id = dc.crosssubjid
			LEFT JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dc.descrid AND dv.studentid=0
			WHERE dv.id IS NULL AND cs.courseid != 0  -- only for those, who have no visibility yet
		";
		g::$DB->execute($sql); //only necessary if we save courseinformation as well -> existing crosssubjects imported  only as drafts -> not needed
		
		//example visibility
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."}
			(courseid, exampleid, studentid, visible)
			SELECT DISTINCT ct.courseid, dc.exampid, 0, 1
			FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
			JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dt.descrid AND dv.studentid=0
			JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} dc ON dc.descrid=dt.descrid
			LEFT JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} ev ON ev.exampleid=dc.exampid AND ev.studentid=0 AND ev.courseid=ct.courseid
			WHERE ev.id IS NULL -- only for those, who have no visibility yet
		";
		g::$DB->execute($sql);
		
		//example solutions visibility
		$sql = "
            INSERT INTO {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."}
            (courseid, exampleid, studentid, visible)
            SELECT DISTINCT ct.courseid, dc.exampid, 0, 1
            FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
            JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
            JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dt.descrid AND dv.studentid=0
            JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} dc ON dc.descrid=dt.descrid
            LEFT JOIN {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."} ev ON ev.exampleid=dc.exampid AND ev.studentid=0 AND ev.courseid=ct.courseid
            WHERE ev.id IS NULL -- only for those, who have no visibility yet
        ";
		g::$DB->execute($sql);
		
		//example visibility crosssubjects
		$sql = "
			INSERT INTO {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."}
			(courseid, exampleid, studentid, visible)
			SELECT DISTINCT cs.courseid, de.exampid, 0, 1
			FROM {".BLOCK_EXACOMP_DB_CROSSSUBJECTS."} cs
			JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON cs.id = dc.crosssubjid
			JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dc.descrid AND dv.studentid=0
			JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON de.descrid=dv.descrid
			LEFT JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} ev ON ev.exampleid=de.exampid AND ev.studentid=0 AND ev.courseid=cs.courseid
			WHERE ev.id IS NULL AND cs.courseid != 0  -- only for those, who have no visibility yet
		";
		g::$DB->execute($sql); //only necessary if we save courseinformation as well -> existing crosssubjects imported  only as drafts -> not needed
		
		//example solution visibility： crosssubjects
		$sql = "
            INSERT INTO {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."}
            (courseid, exampleid, studentid, visible)
            SELECT DISTINCT cs.courseid, de.exampid, 0, 1
            FROM {".BLOCK_EXACOMP_DB_CROSSSUBJECTS."} cs
            JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON cs.id = dc.crosssubjid
            JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON dv.descrid=dc.descrid AND dv.studentid=0
            JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON de.descrid=dv.descrid
            LEFT JOIN {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."} ev ON ev.exampleid=de.exampid AND ev.studentid=0 AND ev.courseid=cs.courseid
            WHERE ev.id IS NULL AND cs.courseid != 0  -- only for those, who have no visibility yet
        ";
		g::$DB->execute($sql); //only necessary if we save courseinformation as well -> existing crosssubjects imported  only as drafts -> not needed
	}
}

class data_exporter extends data {

	/**
	 * @var SimpleXMLElement
	 */
	static $xml;

	/**
	 * @var ZipArchive
	 */
	static $zip;

	static $filter_descriptors;
	
	public static function do_export($filter_descriptors = null) {
		global $SITE;
		
		\core_php_time_limit::raise();
		raise_memory_limit(MEMORY_HUGE);
		
		if (!self::get_my_source()) {
			// this can't happen anymore, because a source is automatically generated
			throw new moodle_exception('source not configured, go to block settings');
			// '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingexacomp">settings</a>'
		}
		
		$xml = new SimpleXMLElement(
			'<?xml version="1.0" encoding="UTF-8"?>'.
			'<exacomp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd" />'
		);
		
		$xml['version'] = '2015081400';
		$xml['date'] = date('c');
		$xml['source'] = self::get_my_source();
		$xml['sourcename'] = $SITE->fullname;
		
		$zip = ZipArchive::create_temp_file();
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
		self::export_assignments($xml, $zip);
		
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
	
	public static function do_activity_export($activityid) {
	    
	    \core_php_time_limit::raise();
	    raise_memory_limit(MEMORY_HUGE);
	    	    
	    $zip = ZipArchive::create_temp_file();
	    
	    self::$zip = $zip;
	    
	    self::export_assignments(null, $zip, $activityid);
	    
	    $zipfile = $zip->filename;
	    	    
	    $zip->close();
	    
	    $filename = 'exacomp-'.strftime('%Y-%m-%d %H%M').'.zip';
	    header('Content-Type: application/zip');
	    header('Content-Length: ' . filesize($zipfile));
	    header('Content-Disposition: attachment; filename="'.$filename.'"');
	    readfile($zipfile);
	    unlink($zipfile);
	    
	    exit;
	}

	/**
	 * @param SimpleXMLElement $xmlItem
	 * @param $dbItem
	 * @throws moodle_exception
	 */
	private static function assign_source($xmlItem, $dbItem) {
		if (@$dbItem->source && @$dbItem->sourceid) {
			if ($dbItem->source == BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT) {
				// source und sourceid vorhanden -> von wo anders erhalten
				throw new moodle_exception('database error, has default source #69fvk3');
			} elseif ($dbItem->source == BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC) {
				// local source -> von dieser moodle instanz selbst
				throw new moodle_exception('database error, has specific source #yt8d21');
			} elseif ($source = self::get_source_global_id($dbItem->source)) {
				$xmlItem['source'] = $source;
				$xmlItem['id'] = $dbItem->sourceid;
			} else {
				throw new moodle_exception('database error, unknown source '.$dbItem->source.' for type '.$xmlItem->getName().' #f9ssaa8');
			}
		} else {
			// local source -> set new id
			$xmlItem['source'] = self::get_my_source();
			$xmlItem['id'] = $dbItem->id;
		}
	}

	/**
	 * @param SimpleXMLElement $xmlItem
	 * @param string $childName
	 * @param string $table
	 * @param int $id
	 * @throws moodle_exception
	 */
	private static function add_child_with_source($xmlItem, $childName, $table, $id) {
		if ($dbItem = g::$DB->get_record($table, array("id" => $id))) {
			self::assign_source($xmlItem->addChild($childName), $dbItem);
		}
	}

	private static function export_file(SimpleXMLElement $xmlItem, \stored_file $file) {
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

	private static function export_skills(SimpleXMLElement $xmlParent) {
		$dbItems = g::$DB->get_records(BLOCK_EXACOMP_DB_SKILLS); // , array("source"=>self::$source));
		
		if (!$dbItems) return;
		
		$xmlItems = $xmlParent->addChild('skills');
		
		foreach ($dbItems as $dbItem) {
			$xmlItem = $xmlItems->addChild('skill');
			self::assign_source($xmlItem, $dbItem);
			$xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
			$xmlItem->sorting = $dbItem->sorting;
		}
	}
	
	private static function export_niveaus(SimpleXMLElement $xmlParent, $parentid = 0) {
		/*
		<niveau id="4">
			<title><![CDATA[B2]]></title>
			<sorting>5632</sorting>
			<niveautexts>
				<niveautext id="89" skillid="1" lang="de"><title><![CDATA[Ich kann...]]></title></niveautext>
			</niveautexts>
		</niveau>
		*/
		$dbItems = g::$DB->get_records(BLOCK_EXACOMP_DB_NIVEAUS, array('parentid'=>$parentid)); // , array("source"=>self::$source));
		
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
	
	private static function export_taxonomies(SimpleXMLElement $xmlParent, $parentid = 0) {
		$dbItems = g::$DB->get_records(BLOCK_EXACOMP_DB_TAXONOMIES, array('parentid'=>$parentid)); // , array("source"=>self::$source));
		
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

	private static function export_examples(SimpleXMLElement $xmlParent, $parentid = 0) {
		/*
		<example id="3" taxid="78">
			<title><![CDATA[Hardware Anschaffungen]]></title>
			<titleshort><![CDATA[Hardware Anschaffungen]]></titleshort>
			<description><![CDATA[Zeitbedarf in Minuten:	30
	Hilfsmittel:	PC mit aktuellem Betriebssystem und MS Windows MovieMaker, Internet; Aufgabenstellung
	Didaktische Hinweise:	Teamarbeit mit 2-3 SchülerInnen. Teilen Sie jedem Team ein Thema zu.]]></description>
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
					FROM {".BLOCK_EXACOMP_DB_DESCEXAMP."} de
					WHERE de.descrid IN (".join(',', self::$filter_descriptors).")
				)
			";
		} else {
			$filter = "";
		}

		/* @var example[] $dbItems */
		$dbItems = example::get_objects_sql("
			SELECT e.*
			FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} e
			WHERE (e.source IS NULL OR e.source != ".BLOCK_EXACOMP_EXAMPLE_SOURCE_USER.") AND
			".($parentid ? "e.parentid = $parentid" : "(e.parentid=0 OR e.parentid IS NULL)")."
			$filter
		");

		if (!$dbItems) return;
		
		$xmlItems = $xmlParent->addChild($parentid ? 'children' : 'examples');

		foreach ($dbItems as $dbItem) {
			$xmlItem = $xmlItems->addChild('example');
			
			// special source handling for examples, if created as teacher, export as my source
			if ($dbItem->source == BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER) {
				$dbItem->source = null;
				$dbItem->sourceid = null;
			}
			self::assign_source($xmlItem, $dbItem);
			$xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
			$xmlItem->addChildWithCDATAIfValue('titleshort', $dbItem->titleshort);
			$xmlItem->addChildWithCDATAIfValue('description', $dbItem->description);
			$xmlItem->addChildWithCDATAIfValue('author', $dbItem->get_author());
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
			
			
			$descriptors = g::$DB->get_records_sql("
				SELECT DISTINCT d.id, d.source, d.sourceid
				FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
				JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON d.id = de.descrid
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
	
	private static function export_descriptors(SimpleXMLElement $xmlParent, $parentid = 0) {
		if (!$parentid && self::$filter_descriptors) {
			$dbItems = g::$DB->get_records_sql("
				SELECT d.*
				FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
				WHERE parentid=0 AND d.id IN (".join(',', self::$filter_descriptors).")
			");
		} else {
			$dbItems = g::$DB->get_records(BLOCK_EXACOMP_DB_DESCRIPTORS, array('parentid'=>$parentid));
		}
		
		if (!$dbItems) return;
		
		$xmlItems = $xmlParent->addChild($parentid ? 'children' : 'descriptors');
		//var_dump($dbItems);
		foreach ($dbItems as $dbItem) {
			$xmlItem = $xmlItems->addChild('descriptor');
			self::assign_source($xmlItem, $dbItem);
			
			self::add_child_with_source($xmlItem, 'skillid', BLOCK_EXACOMP_DB_SKILLS, $dbItem->skillid);
			self::add_child_with_source($xmlItem, 'niveauid', BLOCK_EXACOMP_DB_NIVEAUS, $dbItem->niveauid);
			
			$xmlItem->addChildWithCDATAIfValue('title', $dbItem->title);
			$xmlItem->sorting = $dbItem->sorting;
			$xmlItem->profoundness = $dbItem->profoundness;
			$xmlItem->epop = $dbItem->epop;

			// children
			self::export_descriptors($xmlItem, $dbItem->id);
		}
	}

	private static function export_edulevels(SimpleXMLElement $xmlParent, $parentid = 0) {
		$dbEdulevels = block_exacomp_get_edulevels();

		$xmlEdulevels = SimpleXMLElement::create('edulevels');

		foreach ($dbEdulevels as $dbEdulevel) {
			$xmlSchooltypes = self::export_schooltypes($dbEdulevel);
			if (!$xmlSchooltypes) {
				continue;
			}

			$xmlEdulevel = $xmlEdulevels->addChild('edulevel');
			self::assign_source($xmlEdulevel, $dbEdulevel);
			
			$xmlEdulevel->addChildWithCDATAIfValue('title', $dbEdulevel->title);
			
			$xmlEdulevel->addChild($xmlSchooltypes);
		}

		if ($xmlEdulevels) {
			$xmlParent->addChild($xmlEdulevels);
		}
	}

	private static function export_crosssubjects(SimpleXMLElement $xmlParent) {
		$dbCrosssubjects = block_exacomp_get_crosssubjects();
		$xmlParent->addChild('crosssubjects');

		foreach ($dbCrosssubjects as $dbCrosssubject) {
			$xmlCrosssubject = $xmlParent->crosssubjects->addchild('crosssubject');
			self::assign_source($xmlCrosssubject, $dbCrosssubject);
			
			/* @var SimpleXMLElement $xmlCrosssubject */
			$xmlCrosssubject->addChildWithCDATAIfValue('title', $dbCrosssubject->title);
			$xmlCrosssubject->addChildWithCDATAIfValue('description', $dbCrosssubject->description);
			
			$subject = g::$DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, array('id'=>$dbCrosssubject->subjectid));
			
			if($subject){
				$xmlSubject = $xmlCrosssubject->addChild('subjectid');
				self::assign_source($xmlSubject, $subject);
			}

			$xmlCrosssubject->courseid = $dbCrosssubject->courseid;

			$descriptors = g::$DB->get_records_sql("
				SELECT DISTINCT d.id, d.source, d.sourceid
				FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
				JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON d.id = dc.descrid
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
	
	private static function export_schooltypes($dbEdulevel) {

		$xmlSchooltypes = SimpleXMLElement::create('schooltypes');

		$dbSchooltypes = block_exacomp_get_schooltypes($dbEdulevel->id);

		foreach ($dbSchooltypes as $dbSchooltype) {
			$xmlSubjects = self::export_subjects($dbSchooltype);
			if (!$xmlSubjects) {
				continue;
			}

			$xmlSchooltype = $xmlSchooltypes->addChild('schooltype');
			self::assign_source($xmlSchooltype, $dbSchooltype);
			
			$xmlSchooltype->addChildWithCDATAIfValue('title', $dbSchooltype->title);
			$xmlSchooltype->sorting = $dbSchooltype->sorting;
			$xmlSchooltype->isoez = $dbSchooltype->isoez;
			$xmlSchooltype->epop = $dbSchooltype->epop;
			
			$xmlSchooltype->addChild($xmlSubjects);
		}

		return $xmlSchooltypes;
	}

	private static function export_subjects($dbSchooltype) {
		$xmlSubjects = SimpleXMLElement::create('subjects');

		$dbSubjects = subject::get_objects(array('stid' => $dbSchooltype->id));

		foreach($dbSubjects as $dbSubject){
			$xmlTopics = self::export_topics($dbSubject);
			if (!$xmlTopics) {
				continue;
			}

			$xmlSubject = $xmlSubjects->addChild('subject');
			self::assign_source($xmlSubject, $dbSubject);
			
			$xmlSubject->addChildWithCDATAIfValue('title', $dbSubject->title);
			$xmlSubject->addChildWithCDATAIfValue('titleshort', $dbSubject->titleshort);
			$xmlSubject->addChildWithCDATAIfValue('infolink', $dbSubject->infolink);
			$xmlSubject->addChildWithCDATAIfValue('author', $dbSubject->get_author());
			$xmlSubject->sorting = $dbSubject->sorting;
			$xmlSubject->epop = $dbSubject->epop;

			$xmlSubject->addChild($xmlTopics);

			self::export_subject_niveau_mm($xmlSubject, $dbSubject);
		}

		return $xmlSubjects;
	}

	private static function export_subject_niveau_mm(SimpleXMLElement $xmlSubject, $dbSubject) {
		$dbItems = g::$DB->get_records_sql("
			SELECT n.id, n.source, n.sourceid, sn.subtitle
			FROM {".BLOCK_EXACOMP_DB_NIVEAUS."} n
			JOIN {".BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM."} sn ON sn.niveauid=n.id
			WHERE sn.subjectid = ?
			ORDER BY n.sorting -- actually sorting is not important
		", [$dbSubject->id]);

		if (!$dbItems) {
			return;
		}

		$xmlItems = $xmlSubject->addChild('niveaus');
		foreach ($dbItems as $dbItem) {
			$xmlItem = $xmlItems->addChild('niveau');
			self::assign_source($xmlItem, $dbItem);
			$xmlItem->addChildWithCDATAIfValue('subtitle', $dbItem->get_subtitle());
		}
	}

	private static function export_topics($dbSubject) {
		$xmlTopics = SimpleXMLElement::create('topics');

		if (self::$filter_descriptors) {
			$dbTopics = g::$DB->get_records_sql("
				SELECT t.*
				FROM {".BLOCK_EXACOMP_DB_TOPICS."} t
				WHERE t.subjid = ? AND
					t.id IN (
					SELECT dt.topicid
					FROM {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt
					WHERE dt.descrid IN (".join(',', self::$filter_descriptors).")
				)
			", array($dbSubject->id));
		} else {
			$dbTopics = g::$DB->get_records(BLOCK_EXACOMP_DB_TOPICS, array('subjid' => $dbSubject->id));
		}
		
		foreach($dbTopics as $dbTopic){
			
			$xmlTopic = $xmlTopics->addChild('topic');
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
			
			$descriptors = g::$DB->get_records_sql("
				SELECT DISTINCT d.id, d.source, d.sourceid
				FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
				JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON d.id = dt.descrid
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

		return $xmlTopics;
	}

	private static function export_sources(SimpleXMLElement $xmlParent) {
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

    private static function export_assignments(SimpleXMLElement $xmlParent = null, $zip, $activityid = -1)
    {
        global $CFG, $USER;
        if($activityid==-1){
            
        
        $mm = block_exacomp_get_assigments_of_descrtopic(self::$filter_descriptors);
        $i = 1;
        $dbItem = new \stdClass();

        $xmlItems = $xmlParent->addChild('activities');


        foreach ($mm[0] as $k => $activity) {
            $xmlItem = $xmlItems->addChild('activity');
            $module_type = g::$DB->get_field('course_modules', 'module', array('id' => $k));
            $dbItem->id = $k;
            
            self::assign_source($xmlItem, $dbItem);
            $xmlItem->addChildWithCDATAIfValue('title', $mm[1][$k]);
            $xmlItem->addChildWithCDATAIfValue('type', $module_type);
            foreach ($activity as $ke => $comptype) {
                if ($ke == 0) {
                    
                    $descriptors = g::$DB->get_records_sql("
				        SELECT DISTINCT d.id, d.source, d.sourceid
				        FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
				        JOIN {".BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY."} de ON d.id = de.compid AND de.comptype = 0
				        WHERE de.activityid = ?
			         ", array($dbItem->id));
                    
                    $xmlItem->addChild('descriptors');
                    foreach ($descriptors as $descriptor) {
                        $xmlDescriptor = $xmlItem->descriptors->addChild('descriptorid');
                        self::assign_source($xmlDescriptor, $descriptor);
                    }
                } else {
                    
                    $topics = g::$DB->get_records_sql("
				        SELECT DISTINCT d.id, d.source, d.sourceid
				        FROM {".BLOCK_EXACOMP_DB_TOPICS."} d
				        JOIN {".BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY."} de ON d.id = de.compid AND de.comptype = 1
				        WHERE de.activityid = ?
			         ", array($dbItem->id));
                    
                    $xmlItem->addChild('topics');
                    foreach ($topics as $topic) {
                        $xmlTopic = $xmlItem->topics->addChild('topicid');
                        self::assign_source($xmlTopic, $topic);
                    }
                }
            }
        
            $backupid = moodle_backup($k, $USER->id);
            
            $source = $CFG->dataroot . '/temp/backup/'.$backupid;
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::LEAVES_ONLY);
            
            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (! $file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = 'activities/activity' . $i . '/' . substr($filePath, strlen($source) + 1);
                    
                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }
            
            $i++;
        }
            
        } else {
            $backupid = moodle_backup($activityid, $USER->id);
            $source = $CFG->dataroot . '/temp/backup/'.$backupid;
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::LEAVES_ONLY);
            
            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (! $file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = 'activity/' . substr($filePath, strlen($source) + 1);
                    
                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }
        
        }

//         foreach ($cm_mm->topics as $comp) {
//             foreach ($comp as $cmid) {
//                 moodle_backup($cmid, $USER->id);

//                 $source = glob($CFG->dataroot . '/temp/backup/*');
//                 $source = array_filter($source, 'is_dir');
//                 usort($source, function ($a, $b) {
//                     return filemtime($a) < filemtime($b);
//                 });
//                 if (! isset($source[0])) {
//                     die('backup not found');
//                 }
//                 $source = $source[0];

//                 $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::LEAVES_ONLY);
//                 // $zip->addEmptyDir(basename($source));
//                 foreach ($files as $name => $file) {
//                     // Skip directories (they would be added automatically)
//                     if (! $file->isDir()) {
//                         // Get real and relative path for current file
//                         $filePath = $file->getRealPath();
//                         $relativePath = 'activities/activity'.$i . '/' . substr($filePath, strlen($source) + 1);

//                         // Add current file to archive
//                         $zip->addFile($filePath, $relativePath);
//                     }
//                 }
//             }
//             $i++;
//         }
    }             
}

class data_course_backup extends data {
	public static function assign_source_array($items, $prefix = "") {
		$fld_source = "{$prefix}source";
		$fld_sourceid = "{$prefix}sourceid";
		$fld_id = "{$prefix}id";
		
		foreach ($items as $dbItem) {
			if ($dbItem->$fld_source >= self::MIN_SOURCE_ID) {
				if ($source = data::get_source_global_id($dbItem->$fld_source)) {
					$dbItem->$fld_source = $source;
				} else {
					throw new moodle_exception('database error, unknown source '.$dbItem->$fld_source.' #5555aa8');
				}
			} else {
				// local source -> set new id
				$dbItem->$fld_source = self::get_my_source();
				$dbItem->$fld_sourceid = $dbItem->$fld_id;
			}
		}
		
		return $items;
	}
	
	public static function parse_sourceid($item, $prefix = "") {
		$fld_source = "{$prefix}source";
		$fld_sourceid = "{$prefix}sourceid";
		
		if ($item->$fld_source == self::get_my_source()) {
			$where = array('id' => $item->$fld_sourceid);
		} else {
			if (!$source = self::get_source_from_global_id($item->$fld_source)) {
				return null;
			}
			
			$where = array('source' => $source->id, 'sourceid' => $item->$fld_sourceid);
		}
		
		return $where;
	}
}

class data_importer extends data {
	
	private static $import_source_type;
	private static $import_source_global_id;
	private static $import_source_local_id;
	private static $updateLaterBySources;
	
	private static $import_time = null;

	/**
	 * @var ZipArchive
	 */
	private static $zip;
	
	public static function do_import_string($data = null, $course_template = null, $par_source = BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT ) {
		global $CFG;

		if (!$data) {
			throw new import_exception('data was empty');
		}
		
		$file = tempnam($CFG->tempdir, "zip");
		file_put_contents($file, $data);

		$ret = self::do_import_file($file, $course_template,  $par_source);
		
		@unlink($file);
		
		return $ret;
	}

    /**
     * @param null $url
     * @param null $course_template
     * @param int $par_source
     * @param bool $simulate
     * @param int $schedulerId 0 - not from scheduler; > 0 - scheduler task id, -1 - from main scheduler task '\block_exacomp\task\import'
     * @return bool
     */
	public static function do_import_url($url = null, $course_template = null, $par_source = BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, $simulate = false, $schedulerId = 0) {
		global $CFG;

		if (!$url) {
			throw new import_exception('filenotfound');
		}
		if (file_exists($url)) {
			// it's a file
		    return self::do_import_file($url, $course_template, $par_source, $simulate, $schedulerId);
		}
		
		$file = tempnam($CFG->tempdir, "zip");
		$handle = @fopen($url, 'r');
		if (!$handle) {
			throw new import_exception("could not open url '$url''");
		}

		file_put_contents($file, $handle);
		$ret = self::do_import_file($file, $course_template, $par_source, $simulate, $schedulerId);
		
		@unlink($file);
		
		return $ret;
	}
	
	/**
	 *
	 * @param String $file xml content
	 * @param $course_template of template-course for importing activities
	 * @param int $par_source default is 1, for specific import 2 is used. A specific import can be done by teachers and only effects
	 *         data from topic leven downwards (topics, descriptors, examples)
     * @param bool $simulate need for simulate importing. We can get settings of importing without real importing
     * @param int $schedulerId if it is for scheduler task - id of task; -1 if it is main scheduler task: \block_exacomp\task\import
     * @return bool
	 */
	public static function do_import_file($file = null, $course_template = null, $par_source = BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, $simulate = false, $schedulerId = 0) {
	    global $USER, $CFG, $DB;

		if (!$file) {
			throw new import_exception('filenotfound');
		}
			
		if (!file_exists($file)) {
			throw new import_exception('filenotfound');
		}

		@set_time_limit(0);
		// \core_php_time_limit::raise();
		raise_memory_limit(MEMORY_HUGE);
		
		self::$import_source_type = $par_source;
		self::$import_time = time();
        self::$updateLaterBySources = array();

		// lock import, so only one import is running at the same time
		$lock = Fs::getLock(g::$CFG->tempdir.'/exacomp_import.lock', 0);
		$lock->lock();

        if (!$simulate) {
            //$transaction = g::$DB->start_delegated_transaction();
        }
		
		// guess it's a zip file
		$zip = new ZipArchive();
		$ret = $zip->open($file, ZipArchive::CHECKCONS);
		// TODO: if it is not zip? - possible?
		if ($ret === true) {
			// a zip file
			self::$zip = $zip;

			if (!$xml = $zip->getFromName('data.xml')) {
				throw new import_exception('wrong zip file format');
			}
			
			/*
			 * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
			 * immediate useage
			 */
			$xml = simplexml_load_string($xml, 'block_exacomp\SimpleXMLElement', LIBXML_NOCDATA);
			if (!$xml) {
				throw new import_exception('wrong zip data.xml content');
			}
		} elseif ($ret == ZipArchive::ER_NOZIP) {
			// on error -> try as xml
			/*
			 * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
			 * immediate useage
			 */
			$xml = @simplexml_load_file($file,'block_exacomp\SimpleXMLElement', LIBXML_NOCDATA);
			if (!$xml) {
				throw new import_exception('wrong file not a zipfile and not a data.xml file');
			}
		}

		if(isset($xml->table)){
			throw new import_exception('oldxmlfile');
		}
		
		if (empty($xml['source'])) {
			throw new import_exception('oldxmlfile');
		}
		
		self::$import_source_global_id = (string)$xml['source'];
		if ($simulate || $schedulerId > 0) { // save source for scheduler task
            self::add_source_if_not_exists(self::$import_source_global_id, $schedulerId);
        }
		// get local id
		self::$import_source_local_id =
                self::$import_source_global_id == self::get_my_source() ? 0 : self::add_source_if_not_exists(self::$import_source_global_id);
		// update source name
		if (self::$import_source_local_id) {
			g::$DB->update_record(BLOCK_EXACOMP_DB_DATASOURCES, array(
				'name' => (string)$xml['sourcename']
			), array(
				'id' => self::$import_source_local_id
			));
		}
		
		// update scripts for new source format
		if (self::has_old_data(BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT)) {
			if (self::$import_source_type != BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT) {
				throw new import_exception('you first need to import the default sources!');
			}
			self::move_items_to_source(BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, self::$import_source_local_id);
		}
		else {
			// always move old specific data
			self::move_items_to_source(BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC, self::$import_source_local_id);
		}

		$source_local_id = self::$import_source_local_id;
		if ($simulate) {
            /*$schedulerTaskData = g::$DB->get_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $schedulerId));
            $source_data = g::$DB->get_record(BLOCK_EXACOMP_DB_DATASOURCES, array('source' => $schedulerTaskData->source));
            if (!$source_data && self::$import_source_global_id == self::get_my_source() ) {
                $source_local_id = 0;
            } else {
                $source_local_id = $source_data->id;
            }*/
            //self::$import_source_local_id = $source_local_id;  // TODO: check!
        }
        // or for scheduler task
        if ($schedulerId > 0) {
            $source_local_id = $schedulerId;
            $schedulerTaskData = g::$DB->get_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $schedulerId));
            if (!$schedulerTaskData) {
                throw new import_exception('we have no this task!');
            }
        }

        // work with GetPost, because additional form settings are not initialized yet
        $newSelecting = optional_param_array('selectedGrid', null, PARAM_RAW);
        $currentImportStep = optional_param('currentImportStep', 'compareCategories', PARAM_RAW);
        switch ($currentImportStep) {
            case 'compareCategories':
                // we need to compare assessment_diffLevel_options with XML categories and rename it if needed
                if (isset($xml->categories)) {
                    // work with GetPost, because additional form settings are not initialized yet
                    $newMapping = optional_param_array('changeTo', null, PARAM_RAW);
                    if ($newMapping) {
                        self::update_categorymapping_for_source($source_local_id, $newMapping, $simulate);
                    } else {
                        $difflevels = preg_split("/[\s*,\s*]*,+[\s*,\s*]*/", block_exacomp_get_assessment_diffLevel_options());
                        $categoryMapping = self::get_categorymapping_for_source($source_local_id,
                                ($simulate || $schedulerId > 0 ? true : false));
                        $categories = array();
                        $theSame = true;
                        if (!$newMapping) {
                            foreach ($xml->categories->category as $category) {
                                $categories[] = $category;
                                // mapping must be from real plugin settings
                                /*$mappingExists = $categoryMapping
                                        && array_key_exists(intval($category->attributes()->id), $categoryMapping)
                                        && in_array($categoryMapping[intval($category->attributes()->id)], $difflevels);*/
                                if (!in_array(trim($category->title), $difflevels)/* && !$mappingExists*/) {
                                    $theSame = false;
                                }
                            }
                        }
                        if ((count($categories) > 0 && !$theSame) // for common importing
                                || ($simulate && (!$newMapping && !$newSelecting))) // for scheduler importing
                        {
                            return array('result' => 'compareCategories', 'list' => $categories, 'sourceId' => $source_local_id);
                        }
                    }
                }
            case 'selectGrids':
                // select grids for importing
                if (isset($xml->edulevels)) {
                    $allGridSelected = optional_param('selectedGridAll', null, PARAM_INT);
                    if ($allGridSelected !== null) {
                        self::update_selectedgridsall_for_source($source_local_id, $allGridSelected, ($simulate || $schedulerId > 0 ? true : false));
                    } elseif (!$allGridSelected && $newSelecting && count($newSelecting) > 0) {
                        // update selected grids only if not selected 'all subjects' checkbox
                        self::update_selectedgrids_for_source($source_local_id, $newSelecting, ($simulate || $schedulerId > 0 ? true : false));
                    } else {
                        $selectedGrids = self::get_selectedgrids_for_source($source_local_id,
                                ($simulate || $schedulerId > 0 ? ($schedulerId > 0 ? $schedulerId : true) : false));
                        $grids = array();
                        if ($schedulerId != -1 && (!$newSelecting && $allGridSelected === null && $schedulerId == 0) ||
                                ($simulate && !$newSelecting && $allGridSelected === null)) {
                            foreach ($xml->edulevels->edulevel as $edulevel) {
                                foreach ($edulevel->schooltypes->schooltype as $schooltype) {
                                    foreach ($schooltype->subjects->subject as $subject) {
                                        $subjectUid = intval($subject->attributes()->id);
                                        $subject->pathname = $edulevel->title.' &#9656; '.$schooltype->title;
                                        // selected on previous importing
                                        if ($selectedGrids && array_key_exists($subjectUid, $selectedGrids) &&
                                                $selectedGrids[$subjectUid] == 1) {
                                            $subject->selected = true;
                                        } else if (!$selectedGrids && !$newSelecting) { // first importing for this source
                                            $subject->selected = true; // all subjects are selected
                                        }
                                        // it is new for importing from this source
                                        if ($selectedGrids && !array_key_exists($subjectUid, $selectedGrids)) {
                                            $subject->newForSelected = true;
                                        }
                                        $grids[$subjectUid] = $subject;
                                    }
                                }
                            }
                            $resultByGrids = 'selectGrids';
                            return array('result' => $resultByGrids, 'list' => $grids, 'sourceId' => $source_local_id);
                        }
                    }
                }
            //default:
            //    return array('result' => 'goRealImporting');
        }

        if ($simulate) {
		    return true; // stop importing. It is only simulating
        }

		// self::kompetenzraster_load_current_data_for_source();
		// don't delete all mm_records, because if you import 2 partial xml files the 2nd would overwrite the 1st
		// self::delete_mm_records(self::$import_source_local_id);

        // used for next lists
        $descriptorsFromSelectedGrids = self::get_descriptors_for_subjects_from_xml($xml, $source_local_id, $schedulerId);
        $topicsFromSelectedGrids = self::get_topics_for_subjects_from_xml($xml, $source_local_id, $schedulerId);


		$skillsFromSelected = self::get_property_for_descriptors_from_xml($xml, 'skillid', $descriptorsFromSelectedGrids);
		//self::truncate_table(self::$import_source_local_id, BLOCK_EXACOMP_DB_SKILLS);
		if(isset($xml->skills)) {
			foreach($xml->skills->skill as $skill) {
                if (in_array($skill->attributes()->id, $skillsFromSelected)) {
                    self::insert_skill($skill);
                }
			}
		}

        $niveausFromSelected = self::get_property_for_descriptors_from_xml($xml, 'niveauid', $descriptorsFromSelectedGrids);
		if(isset($xml->niveaus)) {
			foreach($xml->niveaus->niveau as $niveau) {
                if (in_array((int)$niveau->attributes()->id, $niveausFromSelected)) {
                    self::insert_niveau($niveau);
                }
			}
		}
		
		self::truncate_table(self::$import_source_local_id, BLOCK_EXACOMP_DB_TAXONOMIES);
		if(isset($xml->taxonomies)) {
			foreach($xml->taxonomies->taxonomy as $taxonomy) {
				self::insert_taxonomy($taxonomy);
			}
		}

        $categoryMapping = self::get_categorymapping_for_source($source_local_id, ($simulate || $schedulerId > 0 ? true : false));
        $categoryFromSelected = self::get_property_for_descriptors_from_xml($xml, 'categories/categoryid', $descriptorsFromSelectedGrids);
		if (isset($xml->categories)) {
            //$categoryMapping = self::get_categorymapping_for_source(self::$import_source_local_id);
			foreach($xml->categories->category as $category) {
                if (in_array((int)$category->attributes()->id, $categoryFromSelected)) {
                    self::insert_category($category, 0, $categoryMapping);
                }
			}
		}
		
		if (isset($xml->descriptors)) {
			foreach($xml->descriptors->descriptor as $descriptor) {
			    if (in_array((int)$descriptor->attributes()->id, $descriptorsFromSelectedGrids)) {
                    self::insert_descriptor($descriptor, 0, 0, $categoryMapping);
                }
			}
		}

        $examplesFromSelected = self::get_examples_for_descriptors_from_xml($xml, $descriptorsFromSelectedGrids);
		if (isset($xml->examples)) {
			foreach($xml->examples->example as $example) {
			    if (in_array((int)$example->attributes()->id, $examplesFromSelected)) {
                    self::insert_example($example);
                }
			}
			// update eTheMa parents
            if (count(self::$updateLaterBySources) > 0) {
                foreach (self::$updateLaterBySources as $tablename => $tabledata) {
                    foreach ($tabledata as $fieldname => $fielddata) {
                        foreach ($fielddata as $source => $sourcedata) {
                            foreach ($sourcedata as $sourceid) {
                                $where = array('source' => $source, 'sourceid' => $sourceid);
                                if ($dbExist = g::$DB->get_record($tablename, $where)) {
                                    $data = array($fieldname => $dbExist->id);
                                } else {
                                    $data = array($fieldname => 0);
                                }
                                $where = [$fieldname => $sourceid, 'source' => $source];
                                //g::$DB->update_record($tablename, $data, $where); // not for multiple
                                $DB->set_field($tablename, $fieldname, $data[$fieldname], $where);
                            }
                        }
                    }
                }
            }
		}

		if(isset($xml->edulevels)) {
			foreach($xml->edulevels->edulevel as $edulevel) {
				self::insert_edulevel($edulevel, $source_local_id, $schedulerId);
			}
		}
		
	 	if(isset($xml->crosssubjects)) {
			foreach($xml->crosssubjects->crosssubject as $crosssubject) {
				self::insert_crosssubject($crosssubject);
			}
		}
		
		if(isset($xml->sources)) {
			foreach($xml->sources->source as $source) {
				self::insert_source($source);
			}
		}
		

		
		
		
		if( $course_template != 0) {
		    if (isset($xml->activities)) {
		      if ($ret === true) { // only if it is zip
		          extract_zip_subdir($file, "activities", $CFG->tempdir.'/backup', $CFG->tempdir.'/backup');
		      }
		      for ($i = 1;$i <= count($xml->activities->activity); $i++){
		          @rename($CFG->tempdir . '/backup/activities/activity'. $i, $CFG->tempdir . '/backup/activity'. $i);
		          moodle_restore('activity'. $i, $course_template, $USER->id);
		       }
		      @rmdir($CFG->tempdir . '/backup/activities');
		      unlink($CFG->tempdir . '/backup/data.xml');

		  
		      foreach($xml->activities->activity as $activity) {
                  if (isset($activity->descriptors)) {
                      foreach ($activity->descriptors->descriptorid as $descriptorid) {
                          if (in_array((int)$descriptorid->attributes()->id, $descriptorsFromSelectedGrids)) {
                              self::insert_activity($activity, $course_template);
                              continue;
                          }
                      }
                  }
		           if (isset($activity->topics)) {
                       foreach ($activity->topics->topicid as $topicid) {
                           if (in_array((int)$topicid->attributes()->id, $topicsFromSelectedGrids)) {
                               self::insert_activity($activity, $course_template);
                               continue;
                           }
                       }
                   }
		           
		      }
		  }	  
		  $DB->set_field(BLOCK_EXACOMP_DB_SETTINGS, "istemplate", 1, array('courseid' => $course_template));
	   }
		

		// self::kompetenzraster_clean_unused_data_from_source();
		// TODO: was ist mit desccross?
		
		// deaktiviert, das geht so nicht mehr
		// wenn von mehreren xmls mit gleichem source importiert wird, dann loescht der 2te import die descr vom 1ten
		// besprechung 2015-10-06, logic zu delete source uebernehmen und kann dann geloescht werden.
		// self::delete_unused_descriptors(self::$import_source_local_id, self::$import_time, implode(",", $insertedTopics));
		
		
		
		// self::kompetenzraster_clean_unused_data_from_source();
		// TODO: was ist mit desccross?
		
		// deaktiviert, das geht so nicht mehr
		// wenn von mehreren xmls mit gleichem source importiert wird, dann loescht der 2te import die descr vom 1ten
		// besprechung 2015-10-06, logic zu delete source uebernehmen und kann dann geloescht werden.
		// self::delete_unused_descriptors(self::$import_source_local_id, self::$import_time, implode(",", $insertedTopics));
	
		self::normalize_database();
	
		block_exacomp_settstamp();

		//$transaction->allow_commit();
		
		return true;
	}

    /**
     * @param $xpath
     * @param $selectedGrids real list of selected grids
     * @param $subjectsIds can be '*' if this source configured to import ALL grids
     * @param $source_local_id
     * @throws \dml_exception
     */
	private static function DOM_filter_subjects_by_version(&$xpath, $selectedGrids, $subjectsIds, $source_local_id) {
	    global $DB;
	    $result = array();
	    static $subjectVersions = null;
	    if ($subjectVersions === null) {
            $subjectVersions = array();
        }
        if (!array_key_exists($source_local_id, $subjectVersions)) {
            $list = $DB->get_records('block_exacompsubjects', ['source' => $source_local_id]);
            if ($list) {
                $subjectVersions[$source_local_id] = array();
                foreach ($list as $subj) {
                    $subjectVersions[$source_local_id][$subj->sourceid] = $subj->version; // important - not id, but sourceid!
                }
            }
        }
        if (array_key_exists($source_local_id, $subjectVersions)) {
            $existingSubjects = $subjectVersions[$source_local_id];
        } else {
            $existingSubjects = array();
        }
        if (!count($existingSubjects)) {
            return $selectedGrids; // no any subjects in DB yet. All can be imported
        }
        if ($subjectsIds == '*') {
            $subjectsQuery = '//subjects/subject';
        } else {
            $sIds = self::DOM_convert_valuearray_to_xpath_query($selectedGrids, 'id');
            $subjectsQuery = "//subjects/subject[".$sIds."]";
        }
        $subjects = $xpath->query($subjectsQuery);
        if ($subjects->length) {
            foreach ($subjects as $subject) {
                $version = $subject->getElementsByTagName('version');
                if ($version && @$version->item(0)->nodeValue) {
                    $version = $version->item(0)->nodeValue;
                } else {
                    $version = '';
                }
                $sId = $subject->getAttribute('id');
                // compare xml version with database version
                if (array_key_exists($sId, $existingSubjects)) {
                    // exists in DB. Check version with DB
                    if ($existingSubjects[$sId] == '') {
                        // DB record - without version. Import!
                        $result[] = $sId;
                    } elseif ($version != '') {
                        // real compare versions is only here!
                        if (block_exacomp_versions_compare($version, $existingSubjects[$sId])) {
                            $result[] = $sId;
                        }
                    }
                } else {
                    // does not exist in DB - import anycase;
                    $result[] = $sId;
                }
            }
        }
        return $result;
    }

    private static function DOM_convert_valuearray_to_xpath_query($values, $attrName = 'id') {
        array_walk($values, function(&$i) use ($attrName) {
            $i = '@'.$attrName.'="'.$i.'"';
        });
        return implode(" or ", $values);
    }

	private static function get_descriptors_for_subjects_from_xml($xml, $source_local_id, $schedulerId = 0) {
	    global $DB;
	    $result = array();
        $subjectsIds = '';
	    if (self::get_selectedallgrids_for_source($source_local_id, $schedulerId)) {
	        // all subjects
            $subjectsIds = '*';
            $selectedGrids = array();
        } else {
            $selectedGrids = self::get_selectedgrids_for_source($source_local_id, $schedulerId);
            if ($selectedGrids && is_array($selectedGrids)) {
                $selectedGrids = array_filter($selectedGrids, function($v) {
                    return ($v == 1);
                });
                $selectedGrids = array_keys($selectedGrids);
                $subjectsIds = implode(',', $selectedGrids);
            } else {
                return arrray(); // no any selected grid
                //$subjectsIds = '';
            }
        }
        if ($subjectsIds != '') {
            $tempXML = new \DOMDocument();
            $tempXML->loadXML($xml->asXML());
            $xpath = new \DOMXpath($tempXML);
            $selectedGrids = self::DOM_filter_subjects_by_version($xpath, $selectedGrids, $subjectsIds, $source_local_id);
            $query = '';
            // now is only filtered subjects list? right?
            /*if ($subjectsIds == '*') {
                // get from any subject
                $query = "//subjects/subject/topics/topic/descriptors/descriptorid";
            } else*/
            if (count($selectedGrids) > 0) {
                $subjectsIds = self::DOM_convert_valuearray_to_xpath_query($selectedGrids, 'id');
                $query = "//subjects/subject[".$subjectsIds."]/topics/topic/descriptors/descriptorid";
            } else if ($subjectsIds == '*') { // if empty selectedGrids and '*' - check again
                $existingSubjectsForSource = $DB->get_records('block_exacompsubjects', ['source' => $source_local_id]);
                // if no any subject yet (for this source) -> it is new importing
                // if exists at least one - filtered list was cleared
                if (!$existingSubjectsForSource) {
                    $query = "//subjects/subject/topics/topic/descriptors/descriptorid";
                }
            }
            if ($query != '') {
                $descriptors = $xpath->query($query);
                if ($descriptors->length) {
                    foreach ($descriptors as $descriptor) {
                        $result[] = $descriptor->getAttribute('id');
                    }
                }
            }
        }
        return $result;
    }
    
    
    
    private static function get_topics_for_subjects_from_xml($xml, $source_local_id, $schedulerId = 0) {
        $result = array();
        if (self::get_selectedallgrids_for_source($source_local_id, $schedulerId)) {
            // all subjects
            $subjectsIds = '*';
            $selectedGrids = array();
        } else {
            $selectedGrids = self::get_selectedgrids_for_source($source_local_id, $schedulerId);
            if ($selectedGrids && is_array($selectedGrids)) {
                $selectedGrids = array_filter($selectedGrids, function($v) {
                    return ($v == 1);
                });
                $selectedGrids = array_keys($selectedGrids);
                $subjectsIds = implode(',', $selectedGrids);
            } else {
                return array();
            }
        }
        if ($subjectsIds != '') {
            $tempXML = new \DOMDocument();
            $tempXML->loadXML($xml->asXML());
            $xpath = new \DOMXpath($tempXML);
            $selectedGrids = self::DOM_filter_subjects_by_version($xpath, $selectedGrids, $subjectsIds, $source_local_id);
            $query = '';
            // now is only filtered subjects list? right?
            /*if ($subjectsIds == '*') {
                // get from any subject
                $query = "//subjects/subject/topics/topic";
            } else*/
            if (count($selectedGrids) > 0) {
                $subjectsIds = self::DOM_convert_valuearray_to_xpath_query($selectedGrids, 'id');
                $query = "//subjects/subject[".$subjectsIds."]/topics/topic";
            }
            if ($query != '') {
                $topics = $xpath->query($query);
                if ($topics->length) {
                    foreach ($topics as $topic) {
                        $result[] = $topic->getAttribute('id');
                    }
                }
            }
        }
        return $result;
    }

    // used for importing only needed skills, niveus... (from selected grids)
    private static function get_property_for_descriptors_from_xml($xml, $propertyName = 'skillid', $descriptors = array()) {
        $result = array();
        $descriptors = self::DOM_convert_valuearray_to_xpath_query($descriptors, 'id');
        if ($descriptors != '') {
            $query = "//descriptors/descriptor[".$descriptors."]/".$propertyName;
            $tempXML = new \DOMDocument();
            $tempXML->loadXML($xml->asXML());
            $xpath = new \DOMXpath($tempXML);
            $properties = $xpath->query($query);
            if ($properties->length) {
                foreach ($properties as $prop) {
                    $result[] = $prop->getAttribute('id');
                }
            }
        }
        return $result;
    }

    private static function get_examples_for_descriptors_from_xml($xml, $descriptors = array()) {
        $result = array();
        $descriptors = self::DOM_convert_valuearray_to_xpath_query($descriptors, 'id');
        if ($descriptors != '') {
            $query = "//examples/example/descriptors/descriptorid[".$descriptors."]";
            $tempXML = new \DOMDocument();
            $tempXML->loadXML($xml->asXML());
            $xpath = new \DOMXpath($tempXML);
            $descriptors = $xpath->query($query);
            if ($descriptors->length) {
                /** @var \DOMNodeList $descr */
                foreach ($descriptors as $descr) {
                    $result[] = $descr->parentNode->parentNode->getAttribute('id');
                }
            }
            $result = array_unique($result);
        }
        return $result;
    }

	
	private static function insert_or_update_item($table, $item) {
		$where = $item->source ? array('source' => $item->source, 'sourceid' => $item->sourceid) : array('id'=>$item->id);

		// when exporting some niveaus had no title
		// when reimporting use empty title
		if (@$item->title === null) {
			$item->title = '';
		}

		// before inserting decode html entities if needed
		foreach ($item as $key => $value) {
			if (is_string($value) && strpos($value, 'uml;')) {
				$item->$key = html_entity_decode($value);
			}
		}

		if ($dbItem = g::$DB->get_record($table, $where)) {
			$item->id = $dbItem->id;
//echo $item->id.'==='.self::$import_source_local_id."\r\n";
			if ($item->source == self::$import_source_local_id) {
				// only update, if coming from same source as xml
				g::$DB->update_record($table, $item);
			} else {
                // source not xml source -> skip update
            }
		} else {
			$new_id = g::$DB->insert_record($table, $item);
			if ($item->source) {
				// foreign source
				$item->id = $new_id;
			} else {
				// move to specified id
				g::$DB->execute("UPDATE {".$table."} SET id=? WHERE id=?", array($item->id, $new_id));
			}
		}
		return $item->id;
	}
	
	private static function insert_source($xmlItem) {

		if (!$dbSource = self::get_source_from_global_id($xmlItem['id'])) {
			// only for already inserted sources, update them
			return;
		}

		g::$DB->update_record(BLOCK_EXACOMP_DB_DATASOURCES, array(
			'name' => (string)$xmlItem->name
		), array(
			'id' => $dbSource->id,
		));
	}

    private static function update_categorymapping_for_source($sourceId = null, $newMapping = array(), $forSchedulerTask = false) {
	    global $DB;
        $currentMapping = self::get_categorymapping_for_source($sourceId, $forSchedulerTask);
        if ($currentMapping) {
            $newMapping = $newMapping + $currentMapping;
        }
	    $data = serialize($newMapping);
        //$datasql = new \stdClass();
        //$datasql->id = $sourceId;
        //$datasql->category_mapping = $data;
        if ($forSchedulerTask) {
            $DB->execute("UPDATE {".BLOCK_EXACOMP_DB_IMPORTTASKS."} SET category_mapping = ? WHERE id = ?", array($data, $sourceId));
        } else {
            //$DB->update_record(BLOCK_EXACOMP_DB_DATASOURCES, $datasql);
            //$transaction->allow_commit();
            $DB->execute("UPDATE {".BLOCK_EXACOMP_DB_DATASOURCES."} SET category_mapping = ? WHERE id = ?", array($data, $sourceId));
            //print_object($rrr);
            //exit;
        }
        // todo: why is this not working?!:
        /*g::$DB->update_record(BLOCK_EXACOMP_DB_DATASOURCES, array(
                'category_mapping' => $data
        ), array(
                'id' => $sourceId
        ));*/
        return true;
    }

    public static function get_categorymapping_for_source($sourceId = null, $forSchedulerTask = false) {
        $where = array('id' => $sourceId);
        if ($forSchedulerTask) {
            $row = g::$DB->get_field(BLOCK_EXACOMP_DB_IMPORTTASKS, "category_mapping", $where);
        } else {
            $row = g::$DB->get_field(BLOCK_EXACOMP_DB_DATASOURCES, "category_mapping", $where);
        }
        $result = unserialize($row);
        if (!is_array($result)) {
            $result = false;
        }
        return $result;
    }

    private static function update_selectedgridsall_for_source($sourceId = null, $selectedAll = 0, $forSchedulerTask = false) {
        if ($forSchedulerTask) {
            g::$DB->execute("UPDATE {".BLOCK_EXACOMP_DB_IMPORTTASKS."} SET all_grids=? WHERE id=?", array($selectedAll, $sourceId));
        } else {
            g::$DB->execute("UPDATE {".BLOCK_EXACOMP_DB_DATASOURCES."} SET all_grids=? WHERE id=?", array($selectedAll, $sourceId));
        }
    }

    private static function update_selectedgrids_for_source($sourceId = null, $newSelected = array(), $forSchedulerTask = false) {
	    $currentSelected = self::get_selectedgrids_for_source($sourceId, $forSchedulerTask);
	    if ($currentSelected) {
            $newSelected = $newSelected + $currentSelected;
        }
	    $data = serialize($newSelected);
        if ($forSchedulerTask) {
            g::$DB->execute("UPDATE {".BLOCK_EXACOMP_DB_IMPORTTASKS."} SET selected_grids=? WHERE id=?", array($data, $sourceId));
        } else {
            g::$DB->execute("UPDATE {".BLOCK_EXACOMP_DB_DATASOURCES."} SET selected_grids=? WHERE id=?", array($data, $sourceId));
        }
    }

    public static function get_selectedgrids_for_source($sourceId = null, $forSchedulerTask = false) {
        if ($forSchedulerTask > 0) {
            $where = array('id' => $forSchedulerTask);
            $row = g::$DB->get_field(BLOCK_EXACOMP_DB_IMPORTTASKS, "selected_grids", $where);
        } else {
            $where = array('id' => $sourceId);
            $row = g::$DB->get_field(BLOCK_EXACOMP_DB_DATASOURCES, "selected_grids", $where);
        }
        $result = unserialize($row);
        if (!is_array($result)) {
            $result = false;
        }
        return $result;
    }

    public static function get_selectedallgrids_for_source($sourceId = null, $forSchedulerTask = false) {
        if ($forSchedulerTask > 0) {
            $where = array('id' => $sourceId);
            $result = g::$DB->get_field(BLOCK_EXACOMP_DB_IMPORTTASKS, "all_grids", $where);
        } else {
            $where = array('id' => $sourceId);
            $result = g::$DB->get_field(BLOCK_EXACOMP_DB_DATASOURCES, "all_grids", $where);
        };
        return $result;
    }
	
	private static function insert_file($filearea, SimpleXMLElement $xmlItem, $item) {
		if (!self::$zip) {
			return;
		}
		
		$filecontent = self::$zip->getFromName($xmlItem->filepath);

		$fs = get_file_storage();
		
		// delete old file
		$fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', $filearea, $item->id);
		
		// reimport
		$fs->create_file_from_string(array(
			'contextid' => \context_system::instance()->id,
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
	
	protected static function delete_mm_record_for_item($table, $field, $id) {
		$tables = array(
			array(
				'table' => BLOCK_EXACOMP_DB_DESCTOPICS,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('topicid', BLOCK_EXACOMP_DB_TOPICS),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCEXAMP,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('exampid', BLOCK_EXACOMP_DB_EXAMPLES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCCROSS,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('crosssubjid', BLOCK_EXACOMP_DB_CROSSSUBJECTS),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_DESCCAT,
				'mm1' => array('descrid', BLOCK_EXACOMP_DB_DESCRIPTORS),
				'mm2' => array('catid', BLOCK_EXACOMP_DB_CATEGORIES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_EXAMPTAX,
				'mm1' => array('exampleid', BLOCK_EXACOMP_DB_EXAMPLES),
				'mm2' => array('taxid', BLOCK_EXACOMP_DB_TAXONOMIES),
			),
			array(
				'table' => BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM,
				'mm1' => array('subjectid', BLOCK_EXACOMP_DB_SUBJECTS),
				'mm2' => array('niveauid', BLOCK_EXACOMP_DB_NIVEAUS),
			),
		);
		
		$tables = array_filter($tables, function($t) use ($table) { return $t['table'] == $table; });
		if (empty($tables)) {
			throw new moodle_exception("delete_mm_record_for_item: wrong table $table");
		}
		
		$table = reset($tables);
		
		$sql = "DELETE FROM {{$table['table']}}
			WHERE ";
		if ($table['mm1'][0] == $field) {
			$sql .= "{$table['mm1'][0]}=? AND ";
			$sql .= "{$table['mm2'][0]} IN (SELECT id FROM {{$table['mm2'][1]}} WHERE source=?)";
		} elseif ($table['mm2'][0] == $field) {
			$sql .= "{$table['mm2'][0]}=? AND ";
			$sql .= "{$table['mm1'][0]} IN (SELECT id FROM {{$table['mm1'][1]}} WHERE source=?)";
		} else {
			throw new moodle_exception('delete_mm_record_for_item: error');
		}
		
		g::$DB->execute($sql, array($id, self::$import_source_local_id));
	}
	
	private static function insert_niveau($xmlItem, $parent = 0) {
		$item = self::parse_xml_item($xmlItem);

		// TODO: check erweitern und überall reingeben
		/*
		$item = param::clean_object($item, array(
			'source' => PARAM_TEXT,
			'sourceid' => PARAM_INT,
			'title' => PARAM_TEXT
		));
		*/
		$item->parentid = $parent;
		
		self::insert_or_update_item(BLOCK_EXACOMP_DB_NIVEAUS, $item);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_NIVEAUS, $item);
		
		if ($xmlItem->children) {
			foreach ($xmlItem->children->niveau as $child) {
				self::insert_niveau($child, $item->id);
			}
		}
		
		return $item;
	}
	
	private static function insert_example($xmlItem, $parent = 0) {
		$item = self::parse_xml_item($xmlItem);
		$item->parentid = $parent;

        // eTheMa parent - update later
        if (isset($item->ethema_parent)
                && is_array($item->ethema_parent)
                && array_key_exists('@attributes', $item->ethema_parent)
                && array_key_exists('id', $item->ethema_parent['@attributes'])
                && $item->ethema_parent['@attributes']['id'] > 0) {
            if (@$item->ethema_parent['@attributes']['source']) {
                $tempsource = self::add_source_if_not_exists($item->ethema_parent['@attributes']['source']);
            } else {
                $tempsource = self::get_my_source();
            }
            self::$updateLaterBySources[BLOCK_EXACOMP_DB_EXAMPLES]['ethema_parent'][$tempsource][] = $item->ethema_parent['@attributes']['id'];
            $item->ethema_parent = $item->ethema_parent['@attributes']['id'];
        }

        self::insert_or_update_item(BLOCK_EXACOMP_DB_EXAMPLES, $item);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_EXAMPLES, $item);

		// if local example, move to source teacher
		if (!$item->source) {
			g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPLES, array('source' => BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER, 'sourceid'=>null), array("id"=>$item->id));
		}
		
		// has to be called after inserting the example, because the id is needed!
		if ($xmlItem->filesolution) {
			self::insert_file('example_solution', $xmlItem->filesolution, $item);
		}
		if ($xmlItem->filetask) {
			self::insert_file('example_task', $xmlItem->filetask, $item);
		}
		
		self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_EXAMPTAX, 'exampleid', $item->id);
		if ($xmlItem->taxonomies) {
			foreach ($xmlItem->taxonomies->taxonomyid as $taxonomy) {
				if ($taxonomyid = self::get_database_id($taxonomy)) {
					g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPTAX, array("exampleid"=>$item->id, "taxid"=>$taxonomyid));
				}
			}
		}
		
		self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_DESCEXAMP, 'exampid', $item->id);
		if ($xmlItem->descriptors) {
			foreach($xmlItem->descriptors->descriptorid as $descriptor) {
				if ($descriptorid = self::get_database_id($descriptor)) {
					$sql = "SELECT MAX(sorting) as sorting FROM {".BLOCK_EXACOMP_DB_DESCEXAMP."} WHERE descrid=?";
					$max_sorting = g::$DB->get_record_sql($sql, array($descriptorid));
					$sorting = intval($max_sorting->sorting)+1;
					g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCEXAMP, array("exampid"=>$item->id, "descrid"=>$descriptorid, "sorting"=>$sorting));
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
	
	private static function insert_category($xmlItem, $parent = 0, $categoryMapping = array()) {
		$item = self::parse_xml_item($xmlItem);

		// change category title bн category mapping
		if (is_array($categoryMapping) && array_key_exists($item->sourceid, $categoryMapping)) {
		    $mappedCategory = $categoryMapping[$item->sourceid];
		    if ($mappedCategory == '--as_is--') {
		        // leave as it is. nothing to do
            } elseif ($mappedCategory == '--delete--') {
		        // delete category
                return false;
            } else {
                $item->title = trim($categoryMapping[$item->sourceid]);
            }
        }
		
		$item->parentid = $parent;
	
		self::insert_or_update_item(BLOCK_EXACOMP_DB_CATEGORIES, $item);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_CATEGORIES, $item);
		
		if ($xmlItem->children) {
			foreach($xmlItem->children->category as $child) {
				self::insert_category($child, $item->id, $categoryMapping);
			}
		}
		
		return $item;
	}
		
	private static function insert_descriptor($xmlItem, $parent = 0, $sorting = 0, $categoryMapping) {
		$descriptor = self::parse_xml_item($xmlItem);
		$descriptor->crdate = self::$import_time;
		
		if ($parent > 0){
			$descriptor->parentid = $parent;
			$descriptor->sorting = $sorting;
		}
		
		if ($xmlItem->niveauid) {
            $descriptor->niveauid = self::get_database_id($xmlItem->niveauid);
        }
		if ($xmlItem->skillid) {
            $descriptor->skillid = self::get_database_id($xmlItem->skillid);
        }
		if (!isset($descriptor->profoundness)) {
            $descriptor->profoundness = 0;
        }

		self::insert_or_update_item(BLOCK_EXACOMP_DB_DESCRIPTORS, $descriptor);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_DESCRIPTORS, $descriptor);
		
		// if local descriptor, move to custom source
		if (!$descriptor->source) {
			g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('source' => BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR), array("id"=>$descriptor->id));
		}
		
		if ($xmlItem->examples) {
			throw new moodle_exception('oldxmlfile');
		}

		// mm relations
		//self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_DESCCAT, 'descrid', $descriptor->id);
		if ($xmlItem->categories) {
			foreach ($xmlItem->categories->categoryid as $category) {
			    $originCatId = $category['id']->__toString();
                if (is_array($categoryMapping) && array_key_exists($originCatId, $categoryMapping)) {
                    $mappedCategory = $categoryMapping[$originCatId];
                    if ($mappedCategory == '--as_is--') {
                        // leave as is: nothing to do
                    } elseif ($mappedCategory == '--delete--') {
                        // delete: miss this category
                        continue;
                    } else {
                        // change category
                        // but it was already changed before, when categories were imported
                        // so - nothing to do now.
                    }
                }
                if ($categoryid = self::get_database_id($category)) {
                    // TODO: /mantis/view.php?id=3173 - categories (niveaus) are disabled?
                    //g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCCAT, array("descrid"=>$descriptor->id, "catid"=>$categoryid));
                    //g::$DB->delete_records(BLOCK_EXACOMP_DB_DESCCAT, array("descrid"=>$descriptor->id, "catid"=>$categoryid));
                    // 24.04.2019. enable again?
                    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCCAT, array("descrid" => $descriptor->id, "catid" => $categoryid));
                }
			}
		}
		
		if ($xmlItem->children) {
			$sorting = 1;
			foreach ($xmlItem->children->descriptor as $child){
				self::insert_descriptor($child, $descriptor->id, $sorting, $categoryMapping);
				$sorting++;
			}
		}
		
		return $descriptor;
	}
	
	private static function insert_crosssubject($xmlItem) {
		$crosssubject = self::parse_xml_item($xmlItem);
		
		if ($xmlItem->subjectid) {
			$crosssubject->subjectid = self::get_database_id($xmlItem->subjectid);
		}
		self::insert_or_update_item(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $crosssubject);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $crosssubject);

		//crosssubject in DB
		//insert descriptors
		
		self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_DESCCROSS, 'crosssubjid', $crosssubject->id);
		if ($xmlItem->descriptors) {
			foreach($xmlItem->descriptors->descriptorid as $descriptor) {
				if ($descriptorid = self::get_database_id($descriptor)) {
					g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCCROSS, array("crosssubjid"=>$crosssubject->id,"descrid"=>$descriptorid));
				}
			}
		}
		
		return $crosssubject;
	}
		
	private static function insert_taxonomy($xmlItem, $parent = 0) {
		$taxonomy = self::parse_xml_item($xmlItem);
		$taxonomy->parentid = $parent;
	
		self::insert_or_update_item(BLOCK_EXACOMP_DB_TAXONOMIES, $taxonomy);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_TAXONOMIES, $taxonomy);
		
		if ($xmlItem->children) {
			foreach($xmlItem->children->taxonomy as $child) {
				self::insert_taxonomy($child, $taxonomy->id);
			}
		}
		
		return $taxonomy;
	}
	
	private static function insert_topic($xmlItem, $parent = 0) {
		$topic = self::parse_xml_item($xmlItem);
		$topic->parentid = $parent;
		self::insert_or_update_item(BLOCK_EXACOMP_DB_TOPICS, $topic);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_TOPICS, $topic);

		self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_DESCTOPICS, 'topicid', $topic->id);
		if ($xmlItem->descriptors) {
			
			foreach($xmlItem->descriptors->descriptorid as $descriptor) {
				if ($descriptorid = self::get_database_id($descriptor)) {
					g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCTOPICS, array("topicid"=>$topic->id,"descrid"=>$descriptorid));
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

		self::insert_or_update_item(BLOCK_EXACOMP_DB_SUBJECTS, $subject);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_SUBJECTS, $subject);

		foreach ($xmlItem->topics->topic as $topic) {
			$topic->subjid = $subject->id;
			self::insert_topic($topic);
		}

		if ($subject->source == self::$import_source_local_id) {
			// delete and reinsert if coming from same source
			self::delete_mm_record_for_item(BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM, 'subjectid', $subject->id);
		}

		if ($xmlItem->niveaus) {
			foreach ($xmlItem->niveaus->niveau as $niveau) {
				g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM,
					simpleXMLElementToArray($niveau),
					[
						'subjectid' => $subject->id,
						'niveauid' => self::get_database_id($niveau)
					]);
			}
		}

		return $subject;
	}

	private static function insert_schooltype($xmlItem, $source_local_id, $schedulerId = 0) {
		$schooltype = self::parse_xml_item($xmlItem);
        if (self::get_selectedallgrids_for_source(self::$import_source_local_id, $schedulerId)) {
            $subjectsExist = true;
            $allSubjects = true;
        } else {
            $selectedGrids = self::get_selectedgrids_for_source(self::$import_source_local_id, $schedulerId);
            $subjectsExist = self::checkSelectedSubjectIdsInPath($xmlItem, $source_local_id, $schedulerId);
            $allSubjects = false;
        }
        if (!$subjectsExist) {
            return $schooltype;
        }

		self::insert_or_update_item(BLOCK_EXACOMP_DB_SCHOOLTYPES, $schooltype);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_SCHOOLTYPES, $schooltype);
		foreach($xmlItem->subjects->subject as $subject) {
			$subject->stid = $schooltype->id;
            $subjectId = intval($subject->attributes()->id);
			if ($allSubjects || ($selectedGrids && array_key_exists($subjectId, $selectedGrids) && $selectedGrids[$subjectId] == 1)) {
                self::insert_subject($subject);
            }
		}

		return $schooltype;
	}

    /**
     * @param \block_exacomp\SimpleXMLElement $xml
     * @param integer $source_local_id
     * @param integer $schedulerId
     * @return bool
     */
	private static function checkSelectedSubjectIdsInPath($xml, $source_local_id, $schedulerId = 0) {
        if (self::get_selectedallgrids_for_source($source_local_id, $schedulerId)) {
            // all subjects
            $sujectsIds = '*';
        } else {
            if ($schedulerId > 0) {
                $selectedGrids = self::get_selectedgrids_for_source($source_local_id, $schedulerId);
            } else {
                $selectedGrids = self::get_selectedgrids_for_source($source_local_id, false);
            }
            if (is_array($selectedGrids)) {
                $selectedGrids = array_filter($selectedGrids, function($v) {return ($v == 1);});
                $selectedGrids = array_keys($selectedGrids);
                $sujectsIds = self::DOM_convert_valuearray_to_xpath_query($selectedGrids, 'id');
            } else {
                $sujectsIds = '';
            }
        }
        if ($sujectsIds != '') {
            if ($schedulerId == '*') {
                $query = "//subjects/subject";
            } else {
                $query = "//subjects/subject[".$sujectsIds."]";
            }
            // does not work correctly!! why?
            //$subjectsExist = $xml->xpath($query);
            $tempXML = new \DOMDocument();
            $tempXML->loadXML($xml->asXML());
            $xpath = new \DOMXpath($tempXML);
            $subjectsExist = $xpath->query($query);
            if ($subjectsExist->length) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \block_exacomp\SimpleXMLElement $xmlItem
     * @param integer source_local_id
     * @param integer schedulerId
     * @return array|mixed|object
     */
	private static function insert_edulevel($xmlItem, $source_local_id, $schedulerId = 0) {
		$edulevel = self::parse_xml_item($xmlItem);
        $subjectsExist = self::checkSelectedSubjectIdsInPath($xmlItem, $source_local_id, $schedulerId);
        if (!$subjectsExist) {
            return $edulevel;
        }

		self::insert_or_update_item(BLOCK_EXACOMP_DB_EDULEVELS, $edulevel);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_EDULEVELS, $edulevel);

		foreach($xmlItem->schooltypes->schooltype as $schooltype) {
			$schooltype->elid = $edulevel->id;
			self::insert_schooltype($schooltype, $schedulerId);
		}

		return $edulevel;
	}
	
	private static function insert_skill($xmlItem) {
		$skill = self::parse_xml_item($xmlItem);
		
		self::insert_or_update_item(BLOCK_EXACOMP_DB_SKILLS, $skill);
		self::kompetenzraster_mark_item_used(BLOCK_EXACOMP_DB_SKILLS, $skill);

		return $skill;
	}
	
	private static function insert_activity($xmlItem, $course_template){
	    $activity = self::parse_xml_item($xmlItem);	
	    if (isset($xmlItem->descriptors)) {
	        foreach($xmlItem->descriptors->descriptorid as $descriptor) {
	            $descriptorid = self::get_database_id($descriptor);
	            $activityid = self::get_new_activity_id($activity->title, $activity->type, $course_template);
	            block_exacomp_set_compactivity($activityid, $descriptorid, 0, $activity->title); // isset($activity->id) ? intval($activityid) : intval($activity->sourceid)
	        }
	    }
	    if (isset($xmlItem->topics)) {
	        foreach($xmlItem->topics->topicid as $topic) {
	            $topicid = self::get_database_id($topic);
	            $activityid = self::get_new_activity_id($activity->title, $activity->type, $course_template);
	            block_exacomp_set_compactivity($activityid, $topicid, 1, $activity->title); //isset($activity->id) ? intval($activityid) : intval($activity->sourceid)
	        }
	    }
	    
	    return $activity;
	}
	
	public static function get_new_activity_id($activity_title, $activity_type, $course_template){
	    global $DB;
	    $type = $DB->get_field('modules', 'name' , array('id' => $activity_type));
 	    $instance= $DB->get_field($type, 'MAX(id)' , array('name' => $activity_title, 'course' => $course_template));
 	    $id = $DB->get_field('course_modules', 'id', array('instance' => intval($instance), 'deletioninprogress' => 0, 'module' => $activity_type));
	    return $id;
	}
	
	
	
	
	
	private static function parse_xml_item($xml) {
		$item = simpleXMLElementToArray($xml);
		if (isset($item['@attributes'])) {
			$item = $item['@attributes'] + $item;
			unset($item['@attributes']);
		}
		
		$item = (object)$item;

		if (!isset($item->id)) {
			throw new moodle_exception('wrong xml format');
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
		$tableMapping = array(
			'taxonomyid' => BLOCK_EXACOMP_DB_TAXONOMIES,
			'exampleid' => BLOCK_EXACOMP_DB_EXAMPLES,
			'descriptorid' => BLOCK_EXACOMP_DB_DESCRIPTORS,
		    'topicid' => BLOCK_EXACOMP_DB_TOPICS,
			'categoryid' => BLOCK_EXACOMP_DB_CATEGORIES,
			'niveauid' => BLOCK_EXACOMP_DB_NIVEAUS,
			'niveau' => BLOCK_EXACOMP_DB_NIVEAUS,
			'skillid' => BLOCK_EXACOMP_DB_SKILLS,
			'subjectid' => BLOCK_EXACOMP_DB_SUBJECTS
		);
		
		if (isset($tableMapping[$element->getName()])) {
			$table = $tableMapping[$element->getName()];
		} else {
			throw new moodle_exception('get_database_id: wrong element name: '.$element->getName().' '.print_r($element, true));
		}
		
		$item = self::parse_xml_item($element);
		
		$where = $item->source ? array('source' => $item->source, 'sourceid' => $item->sourceid) : array('id'=>$item->id);
		return g::$DB->get_field($table, "id", $where);
	}
	
	
	private static function kompetenzraster_mark_item_used($table, $item) {
		// deactivated for now
	}
	
}
	
/**
 * Moodle prohibits to use SimpleXML Objects as parameter values for $DB functions, therefore we need to convert
 * it to an array, which is done by encoding and decoding it as JSON.
 * Afterwards we need to filter the empty values, otherwise $DB functions throw warnings
 *
 * @param SimpleXMLElement $xmlobject
 * @return array
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

// function to extract a folder of a zip-file to a destination path

function extract_zip_subdir($zipfile, $subpath, $destination, $temp_cache, $traverse_first_subdir=true){
    $zip = new ZipArchive;
//     echo "extracting $zipfile... ";
    if(substr($temp_cache, -1) !== DIRECTORY_SEPARATOR) {
        $temp_cache .= DIRECTORY_SEPARATOR;
    }
    $res = $zip->open($zipfile);
    if ($res === TRUE) {
        if ($traverse_first_subdir==true){
            $zip_dir = $temp_cache . $zip->getNameIndex(0);
        }
        else {
            $temp_cache = $temp_cache . basename($zipfile, ".zip");
            $zip_dir = $temp_cache;
        }
//         echo "  to $temp_cache... \n";
        $zip->extractTo($temp_cache);
        $zip->close();
//         echo "ok\n";
//         echo "moving subdir... ";
//         echo "\n $zip_dir / $subpath -- to -- >  $destination\n";
        @rename($zip_dir . DIRECTORY_SEPARATOR . $subpath, $destination);
//         echo "ok\n";
//         echo "cleaning extraction dir... ";
        rrmdir($zip_dir);
//         echo "ok\n";
    } else {
//         echo "failed\n";
        die();
    }
}

function rrmdir($source, $removeOnlyChildren = false)
{
    if(empty($source) || file_exists($source) === false)
    {
        return false;
    }
    
    if(is_file($source) || is_link($source))
    {
        return unlink($source);
    }
    
    $files = new \RecursiveIteratorIterator
    (
        new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
        );
    //fileInfo (SplFileInfo)
    foreach($files as $fileinfo)
    {
        if($fileinfo->isDir())
        {
            if(rrmdir($fileinfo->getRealPath()) === false)
            {
                return false;
            }
        }
        else
        {
            if(unlink($fileinfo->getRealPath()) === false)
            {
                return false;
            }
        }
    }
    
    if($removeOnlyChildren === false)
    {
        return rmdir($source);
    }
    
    return true;
}

class import_exception extends moodle_exception {
}
