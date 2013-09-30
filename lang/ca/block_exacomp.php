<?php
$string['pluginname'] = 'Competències Exabis';
$string['exabis_competences'] = 'Competències Exabis';

//TABS
//Admin
$string['admintabschooltype'] = "Tipus d'escola";
$string['admintabimport'] = 'Importar';

//Teacher
$string['teachertabconfig'] = 'propietats';
$string['teachertabassignactivities'] = 'Assignar activitats';
$string['teachertabassigncompetences'] = 'Resum de les competències';
$string['teachertabassigncompetencesdetail'] = 'Vista detallada de les competències';
$string['teachertabassigncompetenceexamples'] = 'Exemples i tasques';
$string['teachertabcompetencegrid'] = 'competence grid'; //englisch

//Student
$string['studenttabcompetences'] = 'Resum de les competències';
$string['studenttabcompetencesdetail'] = 'Vista detallada de les competències';
$string['studenttabcompetencesoverview'] = 'Totes les competències assolides';
$string['studenttabcompetenceprofile'] = 'Perfil competencial';
$string['studenttabcompetencesagenda'] = 'learning agenda'; //englisch

//NAVIGATION
$string['studentnavcompetences'] = 'Avaluació de competències';
$string['adminnavconfig'] = 'Configuració dels mòduls';
$string['adminnavimport'] = 'Importar';
$string['teachernavconfig'] = 'Assignatures i temes';
$string['teachernavactivities'] = 'Assignar activitats de Moodle';
$string['teachernavstudents'] = 'Resum de les competències';
$string['teachertabselection'] = 'subject selection';	//englisch

//BREADCRUMBS
$string['teacherbread'] = 'Configuració del curs';
$string['studentbread'] = 'Avaluació de competències';
$string['adminbread'] = 'Assignatures i temes';

//TEXTE
$string['importdone'] = "Les dades ja s'han importat des d'xml";
$string['importpending'] = "No s'han importat les dades!";
$string['doimport'] = 'Importar descriptors';
$string['doimport_again'] = 'Tornar a importar descriptors';
$string['doimport_own'] = 'Importar descriptors individuals';
$string['importsuccess'] = "Les dades s'han importat correctament!";
$string['importsuccess_own'] = "Les dades individuals s'han importat correctament!";
$string['importfail'] = 'Hi ha hagut un error durant la importació';

$string['activitysuccess'] = "S'han aplicat els canvis";

$string['usersubmitted'] = ' ha enviat les tasques següents:';
$string['usersubmittedquiz'] = ' ha completat els jocs de preguntes següents:';
$string['usernosubmission'] = ' no ha enviat cap tasca ni jocs de preguntes associats a aquest descriptor';
$string['descnoassignment'] = "no s'han associat descriptors a aquesta tasca";

$string['specificcontent'] = "temes específics d'aquest lloc";
$string['specificsubject'] = "assignatures específiques d'aquest lloc";
$string['explain_bewertungsschema'] = "Pots especificar el sistema de notes per a aquest curs. Es permeten els nombres de l'1 al 10. L'1 s'utilitza per defecte a la modalitat dicotòmica si s'ha avaluat una competència. Si n és un nombre més gran que 1, aleshores es crea una escala de notes amb n intervals.";
$string['explainnomoodle_config'] = "Per fer servir el mòdul de competències d'exabis, cal que que el teu administrador de Moodle configuri el bloc.";
$string['explainconfig'] = "Per fer servir el mòdul de competències d'exabis, cal triar el teu tipus d'escola. Les dades necessàries s'importaran des de l'arxiu xml.";
$string['explainconfigcourse'] = "Si us plau, tria l'assignatura amb la qual vols treballar en aquest curs.";
$string['explainconfigcourse_subjects'] = "Si us plau, tria els temes que t'agradaria treballar en aquest curs.";
$string['explaineditactivities_subjects'] = 'Aquí pots associar tasques amb descriptors.';
$string['explainassignon'] = "Aquí hi ha una visió de conjunt de tots els alumnes, els descriptors i les tasques amb les quals han estat associats. <br/> Per mostrar l'autoavaluació dels alumnes, prem ";
$string['explainassignoff'] = "Aquí hi ha una visió de conjunt de tots els alumnes, els descriptors i les tasques amb les quals han estat associats. <br/> Per ocultar l'autoavaluació dels alumnes, prem ";
$string['explainassignonstudent'] = "Quines competències has assolit fins ara? Per mostrar l'avaluació del professor/a, prem ";
$string['explainassignoffstudent'] = "Quines competències has assolit fins ara? Per ocultar l'avaluació del professor/a, prem ";
$string['explainevaluationon'] = "Has aconseguit treballar aquestes competències mentre completaves la tasca? Per mostrar l'avaluació del professor/a, prem  ";
$string['explainevaluationoff'] = "Has aconseguit treballar aquestes competències mentre completaves la tasca? Per ocultar l'avaluació del professor/a, prem ";
$string['explainconfigcourse'] = 'Per assignar descriptors a activitats de Moodle cal triar temes.';
$string['explainconfigcourse_subjects'] = 'Per assignar descriptors a activitats de Moodle cal que sel·leccionis les teves assignatures primer i després seleccionar els temes que vols tenir al teu curs.';
$string['explainstudenteditingon'] = 'Aquí hi ha una visió de conjunt de tots els alumnes i les activitats de Moodle que tenen descriptors assignats. Pots avaluar quines competències han assolit els alumnes. <br/> A més, tindràs una visió de conjunt de les autoavaluacions dels alumnes si prems ';
$string['explainstudenteditingoff'] = 'Aquí hi ha una visió de conjunt de tots els alumnes i les activitats de Moodle que tenen descriptors assignats. Pots avaluar quines competències han assolit els alumnes. <br/> Per ocultar les autoavaluacions dels alumnes, prem  ';
$string['explainno_subjects'] = 'Per assignar competències, cal definir activitats en aquest curs';
$string['explainno_comps'] = 'Per avaluar competències, cal que estiguin associades a activitats de Moodle!';
$string['compevaluation'] = 'Trobo que he assolit la competència general per: ';
$string['compdetailevaluation'] = 'Trobo que he assolit la competència general per a aquest exemple: ';
$string['explaincompetencesoverview'] = 'una visió de conjunt de totes les teves competències:';
$string['exacomp:teacher'] = 'visió de conjunt de les accions del professor/a en un curs';
$string['exacomp:admin'] = "visió de conjunt de les accions de l'administrador en un curs";
$string['exacomp:student'] = "visió de conjunt de les accions d'alumnes en un curs";
$string['exacomp:use'] = 'utilitzar les competències Exabis';
$string['exacomp:addinstance'] = 'afegir competències Exabis al curs';
$string['exacomp:myaddinstance'] = "afegir competències Exabis a l'inici";

$string['link_edit_activities'] = 'assignar activitats';
$string['link_import'] = 'importar';
$string['auswahl_speichern'] = 'guardar sel·lecció';
$string['back_to_list'] = 'tornar a la llista';
$string['save'] = "els canvis s'han aplicat";
$string['keineauswahl'] = "no s'han triat assignatures!";
$string['assessedby'] = 'avaluat per: ';
$string['portfolio'] = ' ha pujat els objectes següents per a aquesta competència:<br/>';

$string['examples'] = 'exemples';
$string['sorting'] = "selecciona manera d'ordenar ";
$string['subject'] = 'assignatures';
$string['taxonomies'] = 'taxonomies';
$string['opencomps'] = 'tria les teves competències';
$string['expandcomps'] = 'expandir tot';
$string['contactcomps'] = 'contraure tot';

$string['hier'] = 'aquí';
$string['lehrer_short'] = 'P'; //professor
$string['schueler_short'] = 'A'; //alumnes
$string['keine_beurteilung'] = 'sense avaluació';
$string['reached_competence'] = 'Competència avaluada';
$string['not_met'] = 'competència no assolida';
$string['assigned_acitivities'] = 'Activitats assignades';

$string['no_assigned_acitivities'] = 'No hi ha cap activitat assignada';
$string['assigned_example'] = 'Exemple assignat';
$string['aufgabenstellung'] = 'Tasques';
$string['solution'] = 'Solució';
$string['anhang'] = 'Arxiu adjunt';
$string['externe_aufgabenstellung'] = 'Tasca externa';
$string['gesamtbeispiel'] = 'Exemple complert';
$string['descriptor_task'] = 'El descriptor està assignat a les tasques';
$string['course'] = 'Curs';
$string['erreicht'] = 'avaluat';
$string['gesamt'] = 'Total';
$string['configcourseonce'] = 'Si us plau, configura el curs una vegada.';
$string['createpdf'] = 'Crea un document pdf';
$string['pdfsettings'] = 'Edita les propietats del pdf';
$string['explainprofilesettings'] = 'Quins mòduls cal presentar al perfil de competències?';
$string['explain_exastud_profile_settings'] = "La revisió de l'alumne Exabis guarda revisions en diverses categories al llarg de diversos períodes. Pots seleccionar quisn períodes cal incloure al perfil de competències.";
$string['explain_exacomp_profile_settings'] = 'Mitjançant les competències Exabis, el professorat pot avaluar les teves competències en assignatures diverses. Pots seleccionar quin curs incloure al perfil de competències.';
$string['explain_exaport_profile_settings'] = "El portafoli Exabis s'utilitza per documentar les teves competències durant el teu procés d'aprenentatge individual. Pots seleccionar quins objectes vols incloure al perfil de competències.";

$string['exaportintro'] = 'Introducció';
$string['exaportcategory'] = 'Categoria';
$string['exaporttype'] = 'Tipus';
$string['exaportfilename'] = "Nom d'arxiu";
$string['exaportinfo'] = 'Aquest objecte està associat a les competències següents:';
$string['periodreview'] = 'Avaluació per al període ';
$string['countreviews'] = ' avaluacions realitzades';
$string['detailedreview'] = 'Avaluació detallada';

$string['competence_profile'] = 'Perfil competencial';
$string['infotext'] = 'Aquest document mostra una visió de conjunt de les teves competències actuals. Conté dades de tres mòduls diferents i aporta una visió complerta.';
$string['exacompinfotext'] = 'Les competències que has adquirit prèviament';
$string['exaportinfotext'] = 'Una visió de conjunt dels objectes del portafoli digital que estan associats amb competències';
$string['exastudinfotext'] = "Les teves avaluacions de diferents períodes d'avaluació";

$string['name'] = 'nom';
$string['city'] = 'localitat';
$string['spalten_setting'] = 'Oculta/mostra columnes';
$string['hide_activities'] = 'Selecciona activitats ocultes';
$string['hide_activities_descr'] = 'Si us plau, selecciona activitats que vols ocultar i guardar';
$string['hide_activities_save'] = 'Guardar preferències i selecció';
$string['radargraphheader'] = 'Radar competencial';
$string['notinview'] = "Aquest item encara no s'ha publicat";
$string['bewertungsschema'] = 'Marking scheme'; //englisch
$string['uses_activities'] = 'I work with activites'; //englisch
$string['show_all_descriptors'] = 'Show all descriptors in overview'; //englisch
$string['bewertung'] = ' avaluació: ';
$string['filerequired'] = 'You must upload an file!'; //englisch
$string['compalreadyreached'] = 'The student reached this competence already in another course.'; //englisch
$string['xmlserverurl'] = 'Server-URL'; //englisch
$string['configxmlserverurl'] = 'Url to a xml file, which is used for keeping the database entries up to date'; //englisch
$string['niveau_filter'] = "filter levels"; //englisch
$string['niveau_auswahl'] = "choose niveau"; //englisch
$string['niveau_auswahl_save'] = "filter Niveau!"; //englisch
$string['filter_niveaus_descr'] = "Choose one or more difficulty levels to reduce list above"; //englisch

$string['alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['alternativedatamodel_description'] = 'Tick to use Baden W&uuml;rttemberg Version';
$string['choosesubject'] = "Choose subject: ";
$string['selectall'] = 'Select all';

$string["MO"] = "MON";
$string["DI"] = "TUE";
$string["MI"] = "WED";
$string["DO"] = "THU";
$string["FR"] = "FRI";
$string['todo'] = "What do I do?";
$string['learning'] = "What can I learn?";
$string['student'] = "A";
$string['teacher'] = "P";
$string['assessment'] = "avaluació";
$string['plan'] = "work plan";
$string['example_upload_header'] = 'Upload own example';
$string['activities'] = "activities";
$string['noactivitiesyet'] = "no solutions to this descriptor submitted and no test made";
$string['columnselection'] = 'Table column selection';
$string['allstudents'] = 'All students';
?>