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

-- Add namespace field to table use by MediaWiki Access Log extension
-- Created by: Peter Kin-Fong Fong
-- Date: 18 February 2013

-- --------------------------------------------------------

ALTER TABLE tw_accesslog ADD tw_log_namespace INT( 11 ) NULL DEFAULT NULL AFTER tw_log_username ,
ADD INDEX tw_log_namespace_timestamp ( tw_log_namespace, tw_log_timestamp ), 
ADD INDEX tw_log_title_namespace_timestamp ( tw_log_title ( 255 ) , tw_log_namespace , tw_log_timestamp );
