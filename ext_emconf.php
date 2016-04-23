<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "rn_base".
 *
 * Auto generated 06-01-2016 17:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'A base library for extensions.',
	'description' => 'Uses MVC design principles and domain driven development for TYPO3 extension development.',
	'category' => 'misc',
	'version' => '0.15.0',
	'state' => 'stable',
	'uploadfolder' => true,
	'createDirs' => 'typo3temp/rn_base/',
	'clearcacheonload' => true,
	'author' => 'Rene Nitzsche',
	'author_email' => 'rene@system25.de',
	'author_company' => 'System 25',
	'constraints' => 
	array (
		'depends' => 
		array (
			'cms' => '',
			'typo3' => '4.5.0-6.2.99',
			'php' => '5.3.7-5.6.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

