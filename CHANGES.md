### v4.6.7 (2024031500) ###
* postgres db fix
* small fixes for dakora+ and set
* dakora+: example evaluation is done if item is evaluated

### v4.6.7 (2024020700) ###
* new web-service to get competence profile overview data of all courses
* fix sorting of niveaus, reports and others for dakora+
* webservices for learningpath dakora+


### v4.6.7 (2023121201) ###
* fix xml import issue
* improve performance when loading examples list
* code improvements for external services
* add info if webservices are deactivated when logging in via dakora+ app
* learningpath and reports feature in Dakora+

### v4.6.7 (2023093000) ###
* always allow login on dakoraplus.eu

### v4.6.7 (2023091700) ###
* code cleanup

### v4.6.7 (2023072100) ###
* bug in export corrected
* additional webservices added for dakora+
* updates for Moodle 4.x-versions

### v4.6.7 (2023050901) ###
* empty folder issue
* enhancements of webservices for diggr+ and dakora+apps

### v4.6.7 (20220726) ###
* enhancements of webservices for diggr+ and dakora+apps
* new setting: enable/disable create new grids
* new setting: visibility of teachermade competencies global or only in course

### v4.6.7 (20220606) ###
* Dashboard view of the exacomp Block (examples of student)
* direct link to dakora app in plugin configuration
* moodle 4.0 fixes
* php 8.0 fixes

### v4.6.6 (2022040500) ###
* Performance upgrades for Diggrplus webservices.
* Sesskeys required to prevent XSS attacks.
* Examples created by relating activities to descriptors are only visible if the activities are visible and available. This allows "unlocking" examples by solving previous examples.
* Autotest has been reworked. The grading of examples/descriptors after completing moodle-assignments now does not work with tasks anymore. Instead, events are used. Whenever an assignment is completed, an event is triggered and the related example/descriptor is graded instantly.
* Performance of deleting imported grids has been significantly improved. Warnings if grading data exists have been added.
* Subjects with the field "eidtable" from comet can be edited in exacomp, just like subjects that have been created in exacomp only.
* Updating grids in comet and importing them in exacomp now removes old topics and descriptors from exacomp, if they have been removed in comet.
* Quiz-Questions can now be related to exacomp descriptors. Successfully answering a question leads to automatic grading of the related descriptor.
* Unused/Empty Niveaus in the competence grid are hidden
* MS-Teams users can login to Diggr+ from MS-Teams

### v4.6.6 (2021091100) ###
* Diggrplus functionalities added: Create or update student, create and edit course, personalise competence grading text, create competence based verbal certificate (Zeugnis) for students
* Added fields to students (SPF, ausserordentlicher_schueler) and subjects (is_pflichtgegenstand)
* Substantial performance increase for diggrplus-webservices
* Subjects with childdescriptors work with diggrplus now
* Course specific grading added: Teachers can now choose if they want to use the global exacomp grading settings (default) or if they want to use a different preset grading method for each course individually. The set of presets can be defined in the settings_preconfiguration.xml. This feature works in moodle and dakora for now.
* Moodle activities that have restricted visibility and are related to exacomp competencies now work more coherently with the exacomp examples. If the activity is hidden, the example gets hidden as well, and vice versa. Also, the examples created by relating activities to descriptors that are visible are added to the planning-storage automatically.

### v4.6.5 ###
* weekly scheduler fix jQuery conflict, Layout improvement
* crosssubjects/Themen: Delete related examples if crosssubject is deleted

### v4.6.5 (2021042200) ###
* Annotation for examples added: Diggrplus webservices can annotate examples, e.g. adding a description to an example in a specific course, but not changing the example itself
* If the plugin "moodle-local_komettranslator" is installed, moodle-competency-frameworks can be created from komet-subjects and grading of those moodle-competencies grades the corresponding komet-competencies as well
* URLs to diggrplus installations can now be allowed in the exacomp admin settings
* Notifications are automatically turned on if a user logs into diggr-plus
* Notifications created by dakora and diggrplus webservices now contain "customdata" to describe which app created the notification
* New Field Editor to log which user has changed elements (competencies....), additional to author
* Field Completefile which exists in Komet now also in new and edit example form
* New Feature for Diggr (not diggr plus!): Create pdf-Certificate
* New Configuration Setting for Dakora: Show overview, Show eportfolio

### v4.6.4 (2020120300) ###
* New field teachermaterial. These materials are only shown for the teacher.
* New tab "Overview". The complete grid/subject is displayed as a grid. teachergradings and studentgradings are displayed.

### v4.6.4 (2020091000) ###
* crosssubject total grading added to competence profile
* ICS-Files and links now can be used to import calendars into the exacomp weekly-schedule. The importet appointments act as a special kind of blocking-events and can be shown/hidden by a checkbox.
* Niveaus can now be hidden just like topics and competences
* Automatic gaining of competencies expanded to all activities

### v4.6.4 (2020042000) ###
* interdisciplinary competences: new role for defining courses as haven interdisciplinary competence grids. these grids also have to be marked as interdisciplinary in the comet-tool.
* implementation of grading history
* time-budgets of materials are now available
* implementation of grading history if more trainers grade a student the grading history is shown
* teachers can hide "interdisciplinary subjects" from a course so the will not show in the competence grid they use in the course
* better sorting of linked competences
* average calculation in competence profile if more competences are in one cell
* automatic competentence assessment now works with new scheme: moodle activities and exacomp material
* thematical combined competence grids in competence profile
* translations done
* taxonomy editing now possible
* pre-planning storage works with group functionality
* taxonomy filters moved to view_examples.php
* themes can be assessed now file-upload with additional info for theme competency-based layout in additon group reports with assessment in tabular form change of theme-icon warning if child-competences have had additional assessment mapping to new difficulty levels during import of xml-file selection of xml-file (with more competence grids) during import

### v4.6.3 ###
* themes can be assessed now
* file-upload with additional info for theme
* competency-based layout in additon
* group reports with assessment in tabular form
* change of theme-icon
* warning if child-competences have had additional assessment
* mapping to new difficulty levels during import of xml-file
* selection of xml-file (with more competence grids) during import

### v4.6.2 ###
* changed wording of icons
* Bufixing

### v4.6.1 ###
* new Webservice token.php
* Gruppenberichte

### v4.6.0 ###
* backup/restore of all reviews in course
* bugfixes
* moodle3.3 update

### v4.5.1 ###
* new edit activities logic
* ui improvements
* bugfixes
* Period select dropdown

### v4.5.0 ###
* 3d charts in reports
* ui improvements
* webservice udpates for dakora
* preformance improvements
* "zieldifferenter Unterricht"
* Allow review of a cross-subject if course has no subjects at all
* Allow a cross-subject from multiple subjects (even if they are not selected in this course)
* Shell script to import Competence Grids
* Check if cronjob/shell script is only running one at a time
