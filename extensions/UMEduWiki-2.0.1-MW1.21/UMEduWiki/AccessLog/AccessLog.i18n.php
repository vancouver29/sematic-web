<?php
 /*
  * Copyright (c) 2011-2013 University of Macau
  *
  * Licensed under the Educational Community License, Version 2.0 (the "License");
  * you may not use this file except in compliance with the License. You may
  * obtain a copy of the License at
  *
  * http://www.osedu.org/licenses/ECL-2.0
  *
  * Unless required by applicable law or agreed to in writing,
  * software distributed under the License is distributed on an "AS IS"
  * BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
  * or implied. See the License for the specific language governing
  * permissions and limitations under the License.
  */

 /* File name:	AccessLog.i18n.php
  * Purpose:	International message strings for SpecialAccessLog page
  * Author:		Aleksandar Bojinovic, Peter Kin-Fong Fong
  */

	$messages = array();
 
	$messages['en'] = array(
		'accesslog' => 'Access log',
		'accesslog-badaccess' => 'You do not have access right to this page.',
		'accesslog-disclaimer' => 'This special page lists the access log. For any questions or troubleshooting, please <a href="mailto:$1">contact</a> the administrator.',
		'accesslog-filter-fieldset' => 'Log filtering options',
		'accesslog-filter-access' => 'Access:',
		'accesslog-filter-access-all' => 'all',
		'accesslog-filter-access-view' => 'view',
		'accesslog-filter-access-edit' => 'edit', 
		'accesslog-filter-or' => 'or ',
		'accesslog-filter-title' => 'Title:',
		'accesslog-filter-user' => 'User:',
		'accesslog-filter-user-anons' => '$1 anonymous users',
		'accesslog-filter-user-loggedin' => '$1 logged-in users',
		'accesslog-filter-user-mine' => '$1 my actions',
		'accesslog-filter-daysinput' => 'in last $1 day(s)',
		'accesslog-purge-fieldset' => 'Purge old logs',
		'accesslog-purge-daysinput' => 'Purge log older than $1 day(s) ',
		'accesslog-purge-button' => 'Purge',
		'accesslog-purge-all' => 'Are you sure you want to purge the entire log?',
		'accesslog-purge-days' => 'Are you sure you want to purge entries older than {{PLURAL:$1|one day|$1 days}}?',
		'accesslog-access-view' => 'has viewed',
		'accesslog-access-edit' => 'has edited'
	);
	
?>
