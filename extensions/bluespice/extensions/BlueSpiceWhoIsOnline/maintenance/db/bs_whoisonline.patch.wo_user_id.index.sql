-- Add user_id column index
ALTER TABLE /*$wgDBprefix*/bs_whoisonline
  ADD INDEX (wo_user_id);