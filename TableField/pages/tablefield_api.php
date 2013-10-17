
<?php
# Copyright (C) 2010	GTZ Ethiopia ICT Service
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.


$t_item_list_table = "mantis_TableField_item_list";

class TableFieldApi {
	# Print the value of the custom field (if the field is applicable to the project of
	# the specified issue and the current user has read access to it.
	# see custom_function_default_print_column_title() for rules about column names.
	# $p_column: name of field to show in the column.
	# $p_row: the row from the bug table that belongs to the issue that we should print the values for.
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_value( $p_column, $p_item, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if( COLUMNS_TARGET_CSV_PAGE == $p_columns_target ) {
			$t_column_start = '';
			$t_column_end = '';
			$t_column_empty = '';
		} else {
			$t_column_start = '<td>';
			$t_column_end = '</td>';
			$t_column_empty = '&nbsp;';
		}
	
		echo $t_column_start;
		
		echo $p_item[$p_column];
	
		echo $t_column_end;
	}
	
	function getitem_page_count( $i_itemcount, $i_itemper_page ) {
		$t_itempage_count = ceil( $i_itemcount / $i_itemper_page );
		if( $t_itempage_count < 1 ) {
			$t_itempage_count = 1;
		}
		return $t_itempage_count;
		}
/*
 * @param int $i_itempage_number the page you want to see (set to the actual page on return)
 * @param int $i_itemper_page the number of bugs to see per page (set to actual on return)
 *      -1   indicates you want to see all bugs
 *      null indicates you want to use the value specified in the filter
 * @param int $i_itempage_count you don't need to give a value here, the number of pages will be stored here on return
 * @param int $i_itembug_count you don't need to give a value here, the number of bugs will be stored here on return
*/	
	# Fetch items from the database and return as array
	
	function GetItemRows( $c_field_id, &$i_itempage_number, &$i_itempage_count, &$i_itemper_page, &$i_itembug_count) {
		
		global $t_item_list_table;
		   $i_itemper_page = 40;
           $querys = "SELECT count(*) as count FROM `mantis_TableField_item_list`  WHERE field_id=" .db_param();
           $results = db_query_bound( $querys, Array( $c_field_id ) );
           if ( $row = db_fetch_array( $results ) ){
           $i_itembug_count = $row['count'];

		$i_itempage_count = TableFieldApi::getitem_page_count( $i_itembug_count, $i_itemper_page );
			$from_row = ( $i_itempage_number - 1) * $i_itemper_page;
          }
		$query = "SELECT bug_id, unit, name, supplier, quantity, unit_price, currency
				  FROM $t_item_list_table
				  WHERE field_id=" .db_param()."
				  ORDER BY name
				  LIMIT  $from_row,$i_itemper_page";
			
      	$result = db_query_bound( $query, Array( $c_field_id) );
		$rows = array();
		while ( $row = db_fetch_array( $result) ) {
			$rows[] = $row;

		}
		return $rows;
	}
	
# maybe later ..

}
			$t_column_start = '';
