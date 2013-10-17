<?php
class ReminderPlugin extends MantisPlugin {
	function register() {
		$this->name        = 'Reminder';
		$this->description = 'Sends E-mail to warn for Coming Due Dates';
		$this->version     = '1.01';
		$this->requires    = array('MantisCore'       => '1.2.0',);
		$this->author      = 'Cas Nuy';
		$this->contact     = 'Cas-at-nuy.info';
		$this->url         = 'http://www.nuy.info';
		$this->page			= 'config';
	}

	function hooks() {
		return array(
            'EVENT_MENU_MAIN' => 'menu'
		);
	}
	
 	/*** Default plugin configuration.	 */
	
	function config() {
		return array(
			'reminder_projects_id'			=> 0,
			'reminder_mail_subject'			=> 'Following issue will be Due shortly' ,
			'reminder_days_treshold'		=> 2,
			'reminder_sender'				=> 'admin@example.org',
			'reminder_autenticate_url'		=> '2239b80b693f63f07eafc268853f22dc1abd9192',
		    'reminder_bug_status'			=> ASSIGNED,
			'reminder_ignore_unset'			=> ON,
			'reminder_handler'				=> ON,
			'reminder_group_issues'			=> ON,
			'reminder_group_project'		=> ON,
			'reminder_manager_overview'		=> ON,
			'reminder_group_subject'		=> 'Deadline Overview (Project Manager)',
			'reminder_group_body1'			=> 'Please review the following issues',
			'reminder_group_body2'			=> 'Please do not reply to this message',
			'reminder_project_set'			=> ON,
			);
	}
}
