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

 /* File name:	updateLogRecord.php
  * Purpose:	A maintenance script to convert the old version access log 
  * 			database record to new format. Only tested on MySQL database.
  * Author:		Peter Kin-Fong Fong
  * Date:		18 February 2013
  */

require_once( dirname( __FILE__ ) . "/../../../maintenance/Maintenance.php" );
 
class UpdateAccessLogRecord extends Maintenance {
	
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Convert the old version access log database record to new format";
		$this->setBatchSize( 200 );
	}
	
	public function execute() {
		$this->output( "UMEduWiki AccessLog record update script\n" );
		
		$dbw = wfGetDB( DB_MASTER );
		
		if ( ! $dbw->tableExists( 'tw_accesslog' ) ) {
			$this->error( "tw_accesslog table does not exist", true );
		}
		
		/* Convert rows with null user ID and null namespace ID
		 * in 'tw_accesslog' table and replace with rows with both IDs */
		$nullColsCond = array( 'tw_log_user' => null, 'tw_log_namespace' => null );
		
		$start = $dbw->selectField( 'tw_accesslog', 'MIN(tw_log_id)', $nullColsCond, __METHOD__ );
		if ( !$start ) {
			$this->error( "Nothing to do.", true );
		}
		$end = $dbw->selectField( 'tw_accesslog', 'MAX(tw_log_id)', $nullColsCond, __METHOD__ );
				
		$end += $this->mBatchSize - 1;
		$blockStart = $start;
		$blockEnd = $start + $this->mBatchSize - 1;
		
		while ( $blockEnd <= $end ) {
			$this->output( "...doing tw_log_id from $blockStart to $blockEnd\n" );
			$cond = array_merge( $nullColsCond, array( "tw_log_id BETWEEN $blockStart AND $blockEnd" ) );
			$logRows = $dbw->select( 'tw_accesslog', '*', $cond, __METHOD__ );
			
			$dbw->begin();
			foreach ( $logRows as $row ) {
				// Obtain user ID from user name
				if ( User::isIP( $row->tw_log_username ) ) {
					$tw_log_user_id = 0;
				} else {
					$tw_log_user_id = User::idFromName( $row->tw_log_username );
					if ( $tw_log_user_id === null ) {
						$tw_log_user_id = 0;
					}
				}
				
				// Split namespace from title
				$titleObj = Title::newFromText( $row->tw_log_title );
				$tw_log_namespace = $titleObj->getNamespace();
				$tw_log_title = $titleObj->getDBkey();
				
				$dbw->update( 'tw_accesslog', 
					array(
						'tw_log_user' => $tw_log_user_id, 
						'tw_log_namespace' => $tw_log_namespace, 
						'tw_log_title' => $tw_log_title
					), 
					array( 'tw_log_id' => $row->tw_log_id ),
					__METHOD__
				);
			}
			$dbw->commit();
			
			$blockStart += $this->mBatchSize;
			$blockEnd += $this->mBatchSize;
		}
		
		$this->output( "...Done!\n" );
	}
}
 
$maintClass = 'UpdateAccessLogRecord';
require_once( RUN_MAINTENANCE_IF_MAIN );
