# sql file for hello world module

CREATE TABLE `fha_refresher` (
   `course_id` mediumint(8) unsigned NOT NULL,
   `test_id` mediumint(8) unsigned NOT NULL,
   `enabled` TINYINT DEFAULT '0' NOT NULL,
   `pass_score` VARCHAR( 5 ) NOT NULL ,
   `refresh_period` mediumint(8) unsigned NOT NULL,
   `reminder_period` mediumint(8) unsigned NOT NULL,
   `max_refresh_period` mediumint(8) unsigned NOT NULL,
   PRIMARY KEY ( `course_id` )
);


# refresh_period (number of days since the last successful pass)

# reminder_period (number of days between reminders within refresher_period)

# max_refresh_period (number of days since the last successful pass. not sent if greater than period)


INSERT INTO `language_text` VALUES ('en', '_module','fha_refresher','Test Refresher',NOW(),'');

INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_test','Test',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_pass_score','Pass Score',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_refresher_period','Refresher Period (Days)',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_reminder_period','Reminder Period (Days)',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_max_refresh_period','Maximum Refresh Period (Days)',NOW(),'');

INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_automatic_email_reminder','Automatic Email Reminder',NOW(),'');

INSERT INTO `language_text` VALUES ('en', '_module','fha_ref_automatic_email_body','Greetings!\nThis is an automatic e-mail reminder. According to our records you are due for a course refresher for the following topic:\n %s\nPlease visit https://fhaol.primesignal.com and take the course at your earliest convenience. You will continue receiving these automatic reminders periodically until you take the refresher.\nThanks\nOnline Learning Initiatives\nFraser Health Authority',NOW(),'');


INSERT INTO `language_text` VALUES ('en', '_msgs','AT_ERROR_FHA_REF_MISSING_TEST','You must select a test.',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_msgs','AT_ERROR_FHA_REF_MISSING_SCORE','You must enter a pass score.',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_msgs','AT_ERROR_FHA_REF_MISSING_REF_PERIOD','You must enter a refresher period.',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_msgs','AT_ERROR_FHA_REF_MISSING_REMINDER_PERIOD','You must enter a reminder period.',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_msgs','AT_ERROR_FHA_REF_MISSING_MAX_PERIOD','You must enter a maximum refresher period.',NOW(),'');
INSERT INTO `language_text` VALUES ('en', '_msgs','AT_FEEDBACK_FHA_REF_SAVED','Test refresher saved successuflly.',NOW(),'');

