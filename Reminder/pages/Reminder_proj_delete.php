<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
	require_once( 'core.php' );
	
	auth_reauthenticate();
	
	$f_project_id = gpc_get_int( 'project_id' );
	
	$current_projects = plugin_config_get('reminder_projects_id');
	
	$project_emplode_ids = explode(',', $current_projects);
	
	
	
	foreach ( $project_emplode_ids as $key=>$project_emplode_id){
	
		if ( $project_emplode_id == $f_project_id ) {
	
			unset ( $project_emplode_ids[$key]);
				
			$new_projects = implode(',', $project_emplode_ids);
	
	
			plugin_config_set('reminder_projects_id', $new_projects);
	
		}
	}
	
	form_security_purge( plugin_page( 'Reminder_proj_delete.php' ) );
	
	
	print_successful_redirect( plugin_page( 'config', true ) );
