<?php


# This page sends an E-mail if a due date is getting near
# Authentication is done via an auto-generated token (reminder_key_id) in order to allow using this script easily in scheduled (cron) jobs without any Mantis Login.

require_once( 'core.php' );
$t_core_path = config_get( 'core_path' );
require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'history_api.php' );
require_once( $t_core_path.'email_api.php' );
require_once( $t_core_path.'bugnote_api.php' );

$t_bug_table	= db_get_table( 'mantis_bug_table' );

$t_user_table	= db_get_table( 'mantis_user_table' );

$t_man_table	= db_get_table( 'mantis_project_user_list_table' );

$reminder_key_id = plugin_config_get('reminder_key_id');
$reminder_url = gpc_get('reminder_key_id');
if ( $reminder_key_id !== $reminder_url ) {
	die("reminder_key_id parameter is wrong or missing\n"); // using access_denied() here would be confusion as it redirects to the login page
}

$t_rem_current  = plugin_config_get( 'reminder_projects_id' );
$t_rem_status	= plugin_config_get( 'reminder_bug_status' );
$t_rem_body		= plugin_config_get( 'reminder_mail_subject' );
$t_rem_store	= plugin_config_get( 'reminder_store_as_note' );
$t_rem_handler 	= plugin_config_get( 'reminder_handler' );
$t_rem_group1	= plugin_config_get( 'reminder_group_issues');
$t_rem_group2	= plugin_config_get( 'reminder_group_project' );
$t_rem_manager	= plugin_config_get( 'reminder_manager_overview' );
$r_rem_subject	= plugin_config_get( 'reminder_group_subject' );
$t_rem_body2	= plugin_config_get( 'reminder_group_body1' );
$t_rem_body2	= plugin_config_get( 'reminder_group_body2' );


if ( ON == $t_rem_handler ) {
	$query = "SELECT mantis_bug_table.id,summary,handler_id,project_id,
					round((CAST((due_date - UNIX_TIMESTAMP()) AS SIGNED)/60/60/24)) as timelefts 
		           FROM mantis_bug_table,mantis_user_table \n"
		           ." WHERE mantis_bug_table.handler_id=mantis_user_table.id and status < 70
   					AND  UNIX_TIMESTAMP() + 60*60*24*10 >= due_date and due_date > 2" ;
		           if ( $t_rem_current > ALL_PROJECTS ) {
		           	$query .=" and mantis_bug_table.project_id IN($t_rem_current)" ;
		           }
		           if ( ON == $t_rem_group1 ) {
		           	$query .=" ORDER by handler_id, `timelefts` ASC" ;
		           }

		           if ( OFF== $t_rem_group2 ) {
		           	if ($results) {
		           		while ($t_row = db_fetch_array($results)) {
		           			$t_bug_id 		= $t_row['id'];
		           			$t_recipients	= $t_row['handler_id'];
		           			$results = email_bug_reminder( $t_recipients, $t_bug_id, $t_message);
		           			# Add reminder as bugnote if store reminders option is ON.
		           		}
		           	}
		           } else {
		           	$results = db_query($query);
		           	if ( $results) {

		           		// first group and store reminder per issue
		           		$start = true ;
		           		while ($t_rows = db_fetch_array($results)) {
		           			$t_bug_id 		= $t_rows["id"];
		           			$t_summary		= $t_rows['summary'];
		           			$t_recipients	= $t_rows['handler_id'];
		           			$t_project		= $t_rows['project_id'];
		           			$time_left		= $t_rows['timelefts'];
//		           			var_dump($time_left.__file__.__line__);
		           			}
		           			if ( $start) {
		           				$process_recipients = $t_recipients ;
		           				$start = false ;
		           			}
		           			// collect the bug links for the processing user
		           			if ( $t_recipients == $process_recipients) {
		           				$list   .= "\n";
		           				$lists  .= $t_summary."\n";
		           				$lists .= project_get_name($t_project, "name");
		           				
		           				$lists .= "\n";
		           				if ( $time_left < 0) {
		           					$lists .= $time_left * -1 ." days LATE "."\n";
		           				} else {
//		          				var_dump($time_left.__file__.__line__);
		           					$lists .= $time_left." days left "."\n";
		           				}
		           				$lists  .= string_get_bug_view_url_with_fqdn( $t_bug_id, $process_recipients);
		           				$lists  .="\n";
		           				$lists  .="\n";
		           			}
		           			else {
		           				// now send the grouped email
		           				$body .= "\n";
		           				$body .= $lists ;
		           				$body .= "\n" ;
		           				$body .= $t_rem_body2;
		           				email_group_reminder( $process_recipients,$t_rem_body, $body);
		           				$body = "";
//		           				var_dump($body.__file__.__line__);
		           				$lists = "";
		           				$process_recipients = $t_recipients ;
		           				$lists .= "\n";
		           				$lists .= $t_summary."\n" ;
		           				$lists .= project_get_name($t_project, "name");
//		           				var_dump($lists.__file__.__line__);
		           				$lists .= "\n";
		           				if ( $time_left < 0) {
		           					$lists .= $time_left * -1 ." days LATE "."\n";
		           				} else {
		           					$lists .= $time_left." days left "."\n";
		           				}
		           				$lists .= string_get_bug_view_url_with_fqdn( $t_bug_id, $recipients );
		           				$lists .= " \n";
		           				$lists .="\n";
		           				# Add reminder as bugnote if store reminders option is ON.
		           			}
//		           		}

		           		// handle last one
		           		if ($results){
		           			// now send the grouped email
		           			$body .= $lists."\n" ;
		           			$body .= $t_rem_body2."\n";
//		           			var_dump($body, "This mail send for service agents");
		           			email_group_reminder( $process_recipients,$t_rem_body, $body);
		           			
		           			$body = "";
		           		}
		           	}
		           }
}
if ( ON == $t_rem_manager ) {

	$query = "SELECT mantis_bug_table.id,summary,handler_id,user_id, mantis_project_user_list_table.project_id, round((CAST((due_date - UNIX_TIMESTAMP()) AS SIGNED)/60/60/24)) AS timeleft\n"
	. "FROM mantis_bug_table, mantis_project_user_list_table, mantis_user_table\n"
	. "WHERE mantis_project_user_list_table.user_id=mantis_user_table.id AND status < 70
   			   AND UNIX_TIMESTAMP()+60*60*24*2 >= due_date AND due_date > 10";


	if ( $t_rem_current > ALL_PROJECTS ) {
		$query .=" AND mantis_bug_table.project_id IN( $t_rem_current)" ;
	}

	$query .= " AND mantis_bug_table.project_id = mantis_project_user_list_table.project_id" ;
	$query .= " AND mantis_project_user_list_table.access_level = 70 " ;
	$query .= " ORDER by mantis_project_user_list_table.user_id, timeleft ASC";

	$results = db_query( $query );
	if  ( $results) {
		$start = true ;
		// first group and store reminder per issue
		while ( $t_row = db_fetch_array( $results)) {
			$bug_id 	 = $t_row['id'];
			$summary	 = $t_row['summary'];
			$project	 = $t_row['project_id'];
			$handler	 = $t_row['handler_id'];
			$manager	 = $t_row['user_id'];
			$t_time_left = $t_row['timeleft'];
			if ($start){
				$process_manager = $manager ;
				$start = false ;
			}
			//collect the bug_id by ther project_id no and user_id
			if ($manager==$process_manager){
				$list .= "\n";
				$list .= $summary."\n";
				if ( $t_time_left < 0) {
					$list .= $t_time_left * -1 ." days LATE "."-". " Assigned: ";
				} else {
					$list .= $t_time_left." days left "."-". " Assigned: ";
				}
				$list .= user_get_realname( $handler, "realname" );
				$list .= "\n";
				$list .= project_get_name($project, "name");
				$list .= "\n";
				$list .= string_get_bug_view_url_with_fqdn( $bug_id, $process_manager );
				$list .= "\n";
			} else {
				// now send the grouped email
				$body .= $list."\n";
				$body .= $t_rem_body2."\n";
//				var_dump($body, "This mails send to managers");
				
				$result = email_group_reminder( $process_manager,$r_rem_subject, $body);
				$body = "" ;
				$list = "" ;
				$process_manager = $manager  ;
				$list .= "\n";
				$list .= $summary."\n";
				if ( $t_time_left < 0) {
					$balance = true ;
				} else {
					$balance = false ;
				}
				if ( $balance) {
					$list .= $t_time_left * -1 ." days LATE "."-". " Assigned: ";
				} else {
					$list .= $t_time_left." days left "."-". " Assigned: ";
				}
				$list .= user_get_realname( $handler, "realname" );
				$list .= "\n" ;
				$list .= project_get_name($project, "name");
				$list .= "\n" ;
				$list .= string_get_bug_view_url_with_fqdn( $bug_id, $process_manager );
				$list .= "\n";
				$start = true;

			}
		}
		// handle last one
		if ($results){
			// now send the grouped email
			$body .= $list."\n";
			$body .= $t_rem_body2."\n";
			$body .= "\n" ;
//var_dump($body, "This mails send to managers");
			$result = email_group_reminder( $process_manager,$r_rem_subject, $body);
			$body .= "" ;
		}
	}
}

echo 'Reminder mails were sent.';

# Send Grouped reminder
function email_group_reminder( $t_user_id,$t_rem_subject, $issues ) {
	if ( ($t_username = user_get_field( $t_user_id, 'enabled' ) ) !== 0 ) {
		$t_username = user_get_field( $t_user_id, 'username' );
		$t_email = user_get_email( $t_user_id);
		$t_subject = $t_rem_subject;
		$t_message = $issues ;
	}
	if( !is_blank( $t_email ) ) {
		email_store( $t_email,  $t_subject, $t_message);
		if( OFF == config_get( 'email_send_using_cronjob' ) ) {
			email_send_all();
		}
	}
}
?>
