<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_selected_project             = plugin_config_get('reminder_projects_id');

$t_selected_project_ids 		= explode( ',', $f_selected_project);

$f_reminders_project_id 		= gpc_get_int_array( 'reminder_projects_id', array() );

$merged_project_id				= array_merge($t_selected_project_ids,$f_reminders_project_id);

$f_implode					    = implode( ',', $merged_project_id );

$f_reminder_days_treshold		= gpc_get_int('reminder_days_treshold');
$f_reminder_sender				= gpc_get_string('reminder_sender');
$f_reminder_bug_status			= gpc_get_string('reminder_bug_status');
$f_reminder_mail_subject		= gpc_get_string('reminder_mail_subject');
$f_reminder_handler				= gpc_get_int('reminder_handler');
$f_reminder_group_issues		= gpc_get_int('reminder_group_issues');
$f_reminder_manager_overview	= gpc_get_int('reminder_manager_overview');
$f_reminder_group_subject		= gpc_get_string('reminder_group_subject');
$f_reminder_group_body1			= gpc_get_string('reminder_group_body1');
$f_reminder_group_body2			= gpc_get_string('reminder_group_body2');

plugin_config_set('reminder_projects_id'		, $f_implode);
plugin_config_set('reminder_days_treshold'		, $f_reminder_days_treshold);
plugin_config_set('reminder_sender'				, $f_reminder_sender);
plugin_config_set('reminder_bug_status'			, $f_reminder_bug_status);
plugin_config_set('reminder_mail_subject'		, $f_reminder_mail_subject);
plugin_config_set('reminder_handler'			, $f_reminder_handler);
plugin_config_set('reminder_group_issues'		, $f_reminder_group_issues);
plugin_config_set('reminder_manager_overview'	, $f_reminder_manager_overview);
plugin_config_set('reminder_group_subject'		, $f_reminder_group_subject);
plugin_config_set('reminder_group_body1'		, $f_reminder_group_body1);
plugin_config_set('reminder_group_body2'		, $f_reminder_group_body2);


print_successful_redirect( plugin_page( 'config',TRUE ) );

