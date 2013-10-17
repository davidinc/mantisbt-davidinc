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
	 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'compress_api.php' );
	require_once( 'filter_api.php' );
	require_once( 'last_visited_api.php' );

	require_once( 'tablefield_api.php' );
	
	auth_ensure_user_authenticated();

	$f_itempage_number	= gpc_get_int( 'page_number', 1 );
	$t_field_id = gpc_get_int("field_id");
	
	$rows = TableFieldApi::GetItemRows( $t_field_id, $f_itempage_number, $t_itempage_count,  $t_itemper_page, $t_itembug_count);
	
	$t_row_count = count( $rows );
  
	compress_enable();

	# don't index Item List pages
	html_robots_noindex();

	html_page_top1( plugin_lang_get( 'item_list_link' ) );

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect(  plugin_page( 'view_tablefield_contents_page.php' ) .'?page_number='.$f_itempage_number, current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();

	print_recently_visited();
//	print_page_links($t_page, $t_start, $t_end, $t_current);
//  print_filter_per_page();

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'item_all_inc.php' );

	html_page_bottom( __FILE__ );