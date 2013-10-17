<?php

define( 'CUSTOM_FIELD_TYPE_TABLE', 100 );
define( 'TABLEFIELD_PER_PAGE', 'per_page' );
$t_item_list_table = "mantis_TableField_item_list";


function cfdef_input_textbox_variablesize($p_field_def, $t_custom_field_value) {
	echo '<input ', helper_get_tab_index(), ' type="text" name="custom_field_' . $p_field_def['id'] . '" size="'. $p_field_def['size'] .'"';
	if( 0 < $p_field_def['length_max'] ) {
		echo ' maxlength="' . $p_field_def['length_max'] . '"';
	} else {
		echo ' maxlength="255"';
	}
	echo ' value="' . $t_custom_field_value .'"></input>';
}


// mostly from print_custom_field_input but without calling the event handler ...
function just_print_custom_field_input( $p_field_def , $t_custom_field_value ) {

	global $g_custom_field_type_definition;
	if( isset( $g_custom_field_type_definition[$p_field_def['type']]['#function_print_input'] ) ) {
		call_user_func( $g_custom_field_type_definition[$p_field_def['type']]['#function_print_input'], $p_field_def, $t_custom_field_value );
	} else {
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_DEFINITION, ERROR );
	}

}


function cfdef_prepare_tablefield_value($p_value) {

	echo '<table>';

	echo '<tr>';
	echo '<td margin = 30%;> </td>';
	echo '<td>';
	echo '&nbsp;';
	echo '</td>';
	echo '<td>';
	echo 'Quantity';
	echo '</td>';
	echo '<td>';
	echo 'Unit';
	echo '</td>';
	echo '<td>';
	echo 'Name';
	echo '</td>';
	echo '<td>';
	echo 'Supplier';
	echo '</td>';
	echo '<td>';
	echo 'Unit Price';
	echo '</td>';
	echo '</tr>';

	$output = "";
	$i=0;

	foreach($p_value as $line) {
		$output.= '<td margin = 30%;> </td>';


		$output.= '<td>';
		$output.= ++$i;
		$output.= '.';
		$output.= '</td>';
		$output.='<td>';
		$output.=$line["quantity"];
		$output.= '</td>';
		$output.= '<td>';
		$output.=$line["unit"];
		$output.= '</td>';
		$output.= '<td>';
		$output.=$line["name"];
		$output.= '</td>';
		$output.= '<td>';
		$output.= $line["supplier"];
		$output.= '</td>';
		$output.= '<td>';
		$output.= $line["unit_price"]." ".$line["currency"];
		$output.= '</td>';
		$output.= '</tr>';

	}
	$output.='</table>';

	return $output;
}

// small wrapper function to keep the field def valid
function tablefield_input_table($p_field_def, $t_custom_field_value) {
	die("__FILE__ __LINE__ we could call TableFieldPlugin->print_input");
}




class TableFieldPlugin extends MantisPlugin {
	/**
	 * Column definition
	 * as an array of custom field definitions.
	 * The field definition shall not contain the field "id" - this will be generated automatically
	 */
	private $definition =
	array(
			"quantity" =>
	array("type" => CUSTOM_FIELD_TYPE_FLOAT, "length_max" => 11, "size" => 3 ),
			"unit" =>
	array("type" => CUSTOM_FIELD_TYPE_STRING, "length_max" => 255, "size" => 5 ),
			"name" => 
	array("type" => CUSTOM_FIELD_TYPE_STRING, "length_max" => 255, "size" => 25 ),
			"supplier" =>
	array("type" => CUSTOM_FIELD_TYPE_STRING, "length_max" => 255, "size" => 25 ),
			"unit_price" =>
	array("type" => CUSTOM_FIELD_TYPE_FLOAT, "length_max" => 11, "size" => 5 ),
			"currency" =>
	array("type" => CUSTOM_FIELD_TYPE_ENUM, "possible_values" => "ETB|EUR|USD" )
	);

	function register() {
		$this->name = 'Custom Field: Table';    # Proper name of plugin
		$this->description = 'Adds the Custom Field Type "Table"';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '0.1';     # Plugin version string
		$this->requires = array(    # Plugin dependencies, array of basename => version pairs
            'MantisCore' => '1.2',  #   Should always depend on an appropriate version of MantisBT
		);

		$this->author = 'GTZ Ethiopia ICT Service - Development Team';         # Author/team name
		$this->contact = 'ict-et@gtz.de';        # Author/team e-mail address
		$this->url = '';            # Support webpage
	}

	function hooks() {
		return array(
            'EVENT_CUSTOM_FIELD_DEFS' => 'defs',
			'EVENT_CUSTOM_FIELD_SET_VALUE' => 'set_value',
			'EVENT_BUG_DELETED' => 'remove_bug_id',
			'EVENT_CUSTOM_FIELD_GET_VALUE' => 'get_value',
			'EVENT_CUSTOM_FIELD_GPC_GET' => 'gpc_get',
			'EVENT_CUSTOM_FIELD_GPC_ISSET' => 'gpc_isset',
			'EVENT_CUSTOM_FIELD_PRINT_INPUT' => 'print_input',
			'EVENT_CUSTOM_FIELD_VALIDATE' => 'validate',
		    'EVENT_MENU_MAIN' => 'menus',
		);
	}

	function lang_get( $p_event, $p_translation, $p_string, $p_lang) {
		if ('custom_field_type_enum_string' == $p_string) {
			$p_translation = $p_translation.',' . CUSTOM_FIELD_TYPE_TABLE . ":" . plugin_lang_get("table");
		}
		return $p_translation;
	}


	function gpc_isset( $p_event , $p_var_name, $p_custom_field_type ) {

		if ($p_custom_field_type != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}
		return true;
		return count ( $this->gpc_get("no-event", $p_var_name, $p_custom_field_type, null ) > 0 );
	}

	function gpc_get( $p_event, $p_var_name, $p_custom_field_type, $p_default = null ) {
		if ($p_custom_field_type != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}

		$linecount= gpc_get_int( $p_var_name , 0 );

		//		var_dump($linecount); die(__FILE__ . __LINE__);

		$value = array();
		for ($row = 1; $row < $linecount; $row++ ) {
			$line = array();

			foreach($this->definition as $subfield_name => $subfield_def ) {
				$sub_var_name = $p_var_name . "_". $row."_".$subfield_name;
				$line[ $subfield_name ] = gpc_get_custom_field( $sub_var_name  , $subfield_def["type"]  );

			}

			# TODO: be more smart when detecting empty rows..
			if ( trim($line["name"]) != "" or trim($line["supplier"]) != "") {
				$value[ $row ] = $line;
			}
		}

		//		var_dump($value); die(__FILE__ . __LINE__);

		return $value;

	}

	function get_value($p_event , $c_field_id, $c_bug_id ) {
		global $t_item_list_table;
		
		$t_item_list_table = "mantis_TableField_item_list";

		$t_name = custom_field_get_field( $c_field_id, 'name' );
		$t_type = custom_field_get_field( $c_field_id, 'type' );
		if ($t_type != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}
		$query = "SELECT id, " . implode(",", array_keys($this->definition) ) . "
		
					  FROM $t_item_list_table
					  WHERE field_id=" . db_param() . " AND
					  		bug_id=" . db_param() . " ORDER BY id ASC";
		$result = db_query_bound( $query, Array( $c_field_id, $c_bug_id ) );

		$value = array();
		if( db_num_rows( $result ) > 0 ) {

			while ( $row = db_fetch_array( $result ) ) {
				$row_id = $row["id"];
				unset($row["id"]);
				$value[$row_id] = $row;
			}

			return $value;
			//	return custom_field_database_to_value( db_result( $result ), $row['type'] );

		} else {
			// return custom_field_default_to_value( $t_default_value, $row['type'] );
			return array();
		}

	}
	function remove_bug_id( $p_event, $c_bug_id){
		global $t_item_list_table;
		
		$b_bug_id = (int)$c_bug_id;
		$t_item_list_table = 'mantis_TableField_item_list';
		$query = 'DELETE FROM ' . $t_item_list_table . ' WHERE bug_id = ' . db_param();
		db_query_bound( $query, Array( $b_bug_id ) );
			
	}
	function set_value($p_event, $c_field_id, $c_bug_id, $p_value, $p_log_insert) {
		global $t_item_list_table;

		$t_name = custom_field_get_field( $c_field_id, 'name' );
		$t_type = custom_field_get_field( $c_field_id, 'type' );
		if ($t_type != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}

		$t_item_list_table = "mantis_TableField_item_list";

		# do I need to update or insert this value?
		//		$query = "SELECT name
		//					  FROM $t_item_list_table
		//					  WHERE field_id=" . db_param() . " AND
		//					  		bug_id=" . db_param();
		//		$result = db_query_bound( $query, Array( $c_field_id, $c_bug_id ) );

		//		if( db_num_rows( $result ) > 0 ) {
		//			$query = "UPDATE $t_item_list_table
		//						  SET name=" . db_param() . "
		//						  WHERE field_id=" . db_param() . " AND
		//						  		bug_id=" . db_param();
		//			db_query_bound( $query, Array( $p_value, $c_field_id, $c_bug_id ) );
		//
		//			$row = db_fetch_array( $result );
		//			history_log_event_direct( $c_bug_id, $t_name, custom_field_database_to_value( $row['value'], $t_type ), $p_value );
		//		} else {
		# Always store the value, even if it's the dafault value
		# This is important, as the definitions might change but the
		#  values stored with a bug must not change

		$old_value = $this->get_value("no-event", $c_field_id, $c_bug_id);

		$query = "DELETE FROM $t_item_list_table WHERE field_id = " . db_param() .' AND bug_id = ' . db_param() ;

		db_query_bound( $query, Array( $c_field_id, $c_bug_id ) );

		$query = "INSERT INTO $t_item_list_table
								( id , field_id, bug_id, unit, name , supplier, quantity , unit_price, currency )
							  VALUES
								( " . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
		$id = 0;
		foreach ($p_value as $row) {
			$id++;
			db_query_bound( $query, Array( $id, $c_field_id, $c_bug_id, $row['unit'], $row['name'], $row['supplier'], $row['quantity'], $row['unit_price'], $row['currency']) );

		}

		if ( $p_log_insert ) {
			// compare old with new
			foreach($old_value as $row_no => $old_row) {
				$old_implode = implode("|",$old_row);

				if (isset($p_value[$row_no])) {
					// changed row
					$new_implode = implode("|",$p_value[$row_no]);
					if ( $old_implode != $new_implode )
					history_log_event_direct( $c_bug_id, $t_name, $old_implode, $new_implode );
				}
				else { // deleted row
					history_log_event_direct( $c_bug_id, $t_name, $old_implode , "(deleted)");
				}
			}
				
			// compare new with old: find new lines
			foreach($p_value as $row_no => $new_row) {
				if (! isset($old_value[$row_no])) {
					history_log_event_direct( $c_bug_id, $t_name, "(added)", implode("|",$new_row));
				}

			}
		}



		//		}

		# write it to the database
		# ...


		# if there was an error, use trigger_error or something like that

		return true;
	}



	function print_input($p_event, $p_field_def, $t_custom_field_value) {
		if ($p_field_def["type"] != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}

		$fid = $p_field_def["id"];



		if (!is_array($t_custom_field_value))
		$t_custom_field_value = array();
			


		echo '<table id="cftable">';
		echo '<tr>';
		echo '<td>';
		echo 'Quantity';
		echo '</td>';
		echo '<td>';
		echo 'Unit';
		echo '</td>';
		echo '<td>';
		echo 'Name';
		echo '</td>';
		echo '<td>';
		echo 'Supplier';
		echo '</td>';
		echo '<td>';
		echo 'Unit Price';
		echo '</td>';
		echo '</tr>';

		// TODO: add a bunch of empty fields in case javascript is disabled!
		//		$t_custom_field_value[] =
		//			array("name" => "", "supplier" => "", "unit_price" => "", "quantity" => "","currency" => "ETB");

		echo '<input type="hidden" name="custom_field_' . $p_field_def['id']. '"';
		echo ' value="' . count($t_custom_field_value) .'" id="custom_field_' . $p_field_def['id']. '"></input>';
		$row = 0;

		foreach ($t_custom_field_value as $line) {
			$row++;
			//		for ($row = 0; $row < $linecount; $row++ ) {
			//			$line = $t_custom_field_value[$row];


			echo '<tr>';

			foreach($this->definition as $subfield_name => $subfield_def ) {
				echo '<td>';
				$subfield_def["id"] =  $fid . "_" .  $row . "_" . $subfield_name;
				just_print_custom_field_input($subfield_def, $line[$subfield_name]);
				echo '</td>';
			}

			echo '</tr>';


		}
		echo '</table>';
		echo 'To delete an item, please simply clear name and supplier.';

		echo '<script language="Javascript">'."\n";
		echo 'function tablefield_addline(callingline) {'."\n";
		echo 'linecount_field = document.getElementById("custom_field_' . $p_field_def['id'] . '");'."\n";
		echo 'if (linecount_field.value > callingline) return;';
		echo 'lineno = ++linecount_field.value;';
		echo 'rowtemplate = \'';

		$default_line =
		array("quantity" => "", "unit" => "", "name" => "", "supplier" => "", "unit_price" => "", "currency" => "ETB");

		foreach($this->definition as $subfield_name => $subfield_def ) {
			$subfield_def["id"] =  $fid . "_ROWPLACEHOLDER_" . $subfield_name;
			echo '<td>';

			just_print_custom_field_input($subfield_def, $default_line[$subfield_name]);
			echo '</td>';

		}
		echo '\';'."\n";
		# all dynamically added fields will get the same tabindex (dyn_tabindex)
		echo 'var dyn_tabindex = '. helper_get_tab_index_value() .';'. "\n";
		//	echo 'window.alert(rowtemplate);';
		echo 'var tbl = document.getElementById("cftable");'."\n";
		echo 'var lastRow = tbl.rows.length;'."\n";
		echo 'var iteration = lastRow;'."\n";
		echo 'var row = tbl.insertRow(lastRow);'."\n";
		echo 'rowtemplate = rowtemplate.replace(/<input /g,"<input onKeyUp=\"tablefield_addline(" + lineno + ")\" ");'."\n";
		echo 'rowtemplate = rowtemplate.replace(/tabindex="[0-9]*"/g,"tabindex=\"" + dyn_tabindex + "\"");'."\n";
		echo 'row.innerHTML = rowtemplate.replace(/ROWPLACEHOLDER/g,lineno);'."\n";
		echo '}'."\n";
		// add the first empty line
		echo 'tablefield_addline();'."\n";
		echo '</script>';

		//echo '<input type="button" onClick="tablefield_addline()" value="+">';
			

		return true;

	}

	/**
	 *
	 * @param $p_event Event String
	 * @param $p_value Value that was entered in the Custom Field
	 * @param $p_field_id Field ID
	 * @param $p_field_def Field Definition (row from custom_field_table)
	 */
	function validate($p_event, $p_field_id , $p_value, $p_field_def ) {
		if ($p_field_def["type"] != CUSTOM_FIELD_TYPE_TABLE) {
			return null;
		}
		# TODO: do some validation!

		foreach ($p_value as $line) {
			$row++;

			foreach($this->definition as $subfield_name => $subfield_def ) {
				$subfield_def["id"] =  $fid . "_" .  $row . "_" . $subfield_name;
				// call the validate function of the specific custom field
				if (! custom_field_validate_internal( $subfield_def , $line[$subfield_name]))
				return false;
			}
		}

		return true;

	}

	function menus() {
		$project = helper_get_current_project() ;

		$t_related_custom_field_ids = custom_field_get_linked_ids( $project );
		foreach($t_related_custom_field_ids as $custom_field_id) {
			if (custom_field_type($custom_field_id) == CUSTOM_FIELD_TYPE_TABLE) {
				$def = custom_field_get_definition($custom_field_id);
				$name = $def["name"];
				if (custom_field_has_read_access_by_project_id($custom_field_id, $project)) {
					# add the link only, if the custom field is activated in this project and the user can read it
					return array('<a href="' . plugin_page( 'view_tablefield_contents_page.php' ) . '&field_id=' . $custom_field_id . '">' .  $name . '</a>');
				}
			}
		};
		return null;
	}


	function defs($parameter, $types) {
		global $g_custom_field_types;
		global $g_custom_field_type_definition;
		global $g_custom_field_type_enum_string, $s_custom_field_type_enum_string;

		# Avoid Conflicts with Other Plugins
		if (isset($g_custom_field_types[CUSTOM_FIELD_TYPE_TABLE])) {
			trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
		}

		$g_custom_field_types[CUSTOM_FIELD_TYPE_TABLE] = 'TableFieldPlugin';

		$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_TABLE ] = array (
			'#display_possible_values' => TRUE,
			'#display_valid_regexp' => TRUE,
			'#display_length_min' => TRUE,
			'#display_length_max' => TRUE,
			'#display_default_value' => TRUE,
			'#special_field' => TRUE,
			'#function_return_distinct_values' => null,
			'#function_value_to_database' => null,
			'#function_database_to_value' => null,
			'#function_print_input' => 'tablefield_input_table',
			'#function_string_value' => "cfdef_prepare_tablefield_value",
			'#function_string_value_for_email' => null,
		);

		$g_custom_field_type_enum_string.="," . CUSTOM_FIELD_TYPE_TABLE . ":Table";


	}

}

