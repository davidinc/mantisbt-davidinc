<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
html_page_top1( plugin_lang_get( 'plugin_title' ) );
html_page_top2();
print_manage_menu();


// generate or read access protection key for bug_reminder_mail

$t_auth_keys = sha1( mt_rand() );
if (plugin_config_get ('reminder_key_id') == null) {
	plugin_config_set( 'reminder_key_id' 		,$t_auth_keys);
}
?>

<br />
<form action="<?php echo plugin_page('config_edit.php')?>" method="post">
<table align="center" class="width75" cellspacing="1">
	<tr>
		<td class="form-title" colspan="3"><?php echo plugin_lang_get( 'plugin_title' ) . ': ' . plugin_lang_get( 'config' ) ?>
		<td></td>
	
	</tr>


	<!-- Assigned Project Selection -->
	<tr <?php echo helper_alternate_class( ) ?>>
		<td class="category" width="25%"><?php 
		echo plugin_lang_get( 'link_the_current_project' ) ;
		?>
		<td><?php

		$t_mantis_project_table = db_get_table( 'mantis_project_table' );

		$t_project_ids = plugin_config_get( 'reminder_projects_id' );

	 $query = "SELECT DISTINCT id, name
				FROM $t_mantis_project_table 
				WHERE id IN (0$t_project_ids)";

	 $result = db_query($query);
	 $category_count = db_num_rows( $result );
	 for( $i = 0;$i < $category_count;$i++ ) {
	 	$row = db_fetch_array( $result );
	 	$t_selected_project_id = $row['id'];
	 	$t_project_name = project_get_name($t_selected_project_id, 'name' );

			echo $t_project_name;

			if( $t_selected_project_id) {

			 print_bracket_link( plugin_page( 'Reminder_proj_delete' ) . '&project_id='.$t_selected_project_id, plugin_lang_get( 'remove_link' ));
			 	
			}

			echo '<br />';
		}

		?>
		</form>
		</select></td>
		<td></td>
		</td>
		<br />
		<td align="center" class="width50" cellspacing="1"><!-- Unassigend Project Selection -->


		<tr <?php echo helper_alternate_class( ) ?> valign="top">
			<td class="category"><?php echo plugin_lang_get( 'projects_title' ) ?>
			</td>
			<td><select name="reminder_projects_id[]" multiple="multiple"
				size="5">
				<?php
				print_project_option_list(NULL, false);
				?>
			</select></td>
			<td></td>
		</tr>

		<!-- Submit Buttom -->
		<tr>
			<td class="center" colspan="5"><input type="submit" class="button"
				value="<?php echo plugin_lang_get( 'activate_projects_button' ) ?>" />
			</td>
		</tr>


		<td class="category" width="60%"><?php echo plugin_lang_get( 'mail_subject' ) ?>
		</td>
		<td width="20%"><textarea NAME="reminder_mail_subject" rows=3 cols=50><?php echo plugin_config_get( 'reminder_mail_subject' )?></textarea>
		</td>

		<td></td>
	
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'sender' ) ?>
		</td>
		<td width="20%"><input type="text" name="reminder_sender" size="50"
			maxlength="50"
			value="<?php echo plugin_config_get( 'reminder_sender' )?>"></td>
		<td></td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'days_treshold' ) ?>
		</td>
		<td width="20%"><input type="text" name="reminder_days_treshold"
			size="3" maxlength="3"
			value="<?php echo plugin_config_get( 'reminder_days_treshold' )?>"></td>
		<td></td>
	</tr>


	<tr <?php echo helper_alternate_class() ?>>
		<td class="category"><?php echo plugin_lang_get( 'manager_overview' ) ?>
		</td>
		<td class="right"><label><input type="radio"
			name="reminder_manager_overview" value="1"
			<?php echo ( ON == plugin_config_get( 'reminder_manager_overview' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_enabled' ) ?></label></td>
		<td class="center"><label><input type="radio"
			name="reminder_manager_overview" value="0"
			<?php echo ( OFF == plugin_config_get( 'reminder_manager_overview' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_disabled' ) ?></label></td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category"><?php echo plugin_lang_get( 'handler' ) ?></td>
		<td class="right"><label><input type="radio" name="reminder_handler"
			value="1"
			<?php echo ( ON == plugin_config_get( 'reminder_handler' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_enabled' ) ?></label></td>
		<td class="center"><label><input type="radio" name="reminder_handler"
			value="0"
			<?php echo ( OFF == plugin_config_get( 'reminder_handler' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_disabled' ) ?></label></td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category"><?php echo plugin_lang_get( 'group_issues' ) ?></td>
		<td class="right"><label><input type="radio"
			name="reminder_group_issues" value="1"
			<?php echo ( ON == plugin_config_get( 'reminder_group_issues' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_enabled' ) ?></label></td>
		<td class="center"><label><input type="radio"
			name="reminder_group_issues" value="0"
			<?php echo ( OFF == plugin_config_get( 'reminder_group_issues' ) ) ? 'checked="checked" ' : ''?> />
			<?php echo plugin_lang_get( 'store_disabled' ) ?></label></td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'group_subject' ) ?>
		</td>
		<td width="20%"><textarea NAME="reminder_group_subject" rows=3 cols=50><?php echo plugin_config_get( 'reminder_group_subject' )?></textarea>
		</td>
		<td></td>
	</tr>
	
	
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'group_body1' ) ?>
		</td>
		<td width="20%"><textarea NAME="reminder_group_body1" rows=3 cols=50><?php echo plugin_config_get( 'reminder_group_body1' )?></textarea>
		</td>
		<td></td>
	</tr>


	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'group_body2' ) ?>
		</td>
		<td width="20%"><textarea NAME="reminder_group_body2" rows=3 cols=50><?php echo plugin_config_get( 'reminder_group_body2' )?></textarea>
		</td>
		<td></td>
	</tr>


	<tr>
		<td class="center" colspan="3"><input type="submit" class="button"
			value="<?php echo plugin_lang_get( 'update_config' ) ?>" /></td>
	</tr>

</table>
<form>
<table>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="60%"><?php echo plugin_lang_get( 'reminder_url' ) ?>

		</td>
		<td><?php 
		$t_host = $_SERVER['SERVER_NAME'];
		echo   $dave = rtrim( 'http://'.$t_host. plugin_page( 'bug_reminder_mail.php').'&reminder_key_id='.plugin_config_get('reminder_key_id') );
			
			
		?></td>

		<td></td>
	</tr>
</table>
		<?php
		html_page_bottom1( __FILE__ );