-- Copyright (c) 2011-2013 University of Macau
--
-- Licensed under the Educational Community License, Version 2.0 (the "License");
-- you may not use this file except in compliance with the License. You may
-- obtain a copy of the License at
--
-- http://www.osedu.org/licenses/ECL-2.0
--
-- Unless required by applicable law or agreed to in writing,
-- software distributed under the License is distributed on an "AS IS"
-- BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
-- or implied. See the License for the specific language governing
-- permissions and limitations under the License.

-- --------------------------------------------------------

-- Table for MediaWiki Access Log extension
-- Created by: Peter Kin-Fong Fong
-- Date: 18 February 2013

-- --------------------------------------------------------

--
-- Table structure for table 'tw_accesslog'
--

CREATE TABLE /*$wgDBprefix*/tw_accesslog (
  tw_log_id int(10) unsigned NOT NULL AUTO_INCREMENT,  -- Unique ID of log entry
  tw_log_timestamp binary(14) NOT NULL DEFAULT '19700101000000',  -- timestamp of the action, in MediaWiki format
  tw_log_user int(10) unsigned DEFAULT NULL,  -- User ID of the action performer
  tw_log_username varbinary(255) NOT NULL,  -- User name of the action performer
  tw_log_namespace int(11) DEFAULT NULL,
  tw_log_title blob NOT NULL,  -- Title of the page
  tw_log_action varbinary(32) NOT NULL,  -- Name of action
  PRIMARY KEY (tw_log_id), 
  KEY tw_log_timestamp (tw_log_timestamp), 
  KEY tw_log_user_timestamp (tw_log_user,tw_log_timestamp), 
  KEY tw_log_namespace_timestamp (tw_log_namespace,tw_log_timestamp), 
  KEY tw_log_title_namespace_timestamp (tw_log_title(255),tw_log_namespace,tw_log_timestamp)
) /*$wgDBTableOptions*/;
