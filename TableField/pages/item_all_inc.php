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
				 * requires current_user_api
				 */
				require_once( 'current_user_api.php' );
				/**
				 * requires bug_api
				 */
				require_once( 'bug_api.php' );
				/**
				 * requires string_api
				 */
				require_once( 'string_api.php' );
				/**
				 * requires date_api
				 */
				require_once( 'date_api.php' );
				/**
				 * requires icon_api
				 */
				require_once( 'icon_api.php' );
				/**
				 * requires columns_api
				 */
				require_once( 'columns_api.php' );
			
				
				require_once( 'tablefield_api.php' );
				
					
				$t_filter = current_user_get_bug_filter();
			
				if( $t_filter ) {
					list( $t_sort, ) = explode( ',', $t_filter['sort'] );
					list( $t_dir, ) = explode( ',', $t_filter['dir'] );
				}
			
				$t_checkboxes_exist = false;
			
				$t_icon_path = config_get( 'icon_path' );
				$t_update_bug_threshold = config_get( 'update_bug_threshold' );
			
				# Improve performance by caching category data in one pass
				if ( helper_get_current_project() > 0 ) {
					category_get_all_rows( helper_get_current_project() );
				} else {
					$t_categories = array();
					foreach ($rows as $t_row) {
						$t_categories[] = $t_row->category_id;
					}
				
					category_cache_array_rows( array_unique( $t_categories ) );
				}
				
				$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );
				
				#TODO make columns customizable in the plugin's configuration similar to manage_config_columns_page.php
			  
				//die( bug_format_id) );
					
				$t_columns = array( 'unit', 'name' , 'supplier', 'quantity' , 'unit_price', 'currency');
				                
			
				$col_count = count( $t_columns );
			
				$t_filter_position = config_get( 'filter_position' );
			
				
				# -- ====================== end of FILTER FORM ================== --
			
			
				# -- ====================== BUG LIST ============================ --
			
				$t_status_legend_position = config_get( 'status_legend_position' );
			
				if ( $t_status_legend_position == STATUS_LEGEND_POSITION_TOP || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
					html_status_legend();
				}
			
				/** @todo (thraxisp) this may want a browser check  ( MS IE >= 5.0, Mozilla >= 1.0, Safari >=1.2, ...) */
				if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
					?>
					<script type="text/javascript" language="JavaScript">
					<!--
						var string_loading = '<?php echo lang_get( 'loading' );?>';
					// -->
					</script>
					<?php
						html_javascript_link( 'xmlhttprequest.js');
						html_javascript_link( 'addLoadEvent.js');
						html_javascript_link( 'dynamic_filters.js');
				}
			?>
			<br />
			<form name="bug_action" method="get" action="bug_actiongroup_page.php">
			<table id="buglist" class="width100" cellspacing="1">
			<tr>
				<td class="form-title" colspan="<?php echo $col_count - 2; ?>">
					<?php
						# -- Viewing range info --
			
						$v_start = 0;
						$v_end   = 0;
			
						if ( count( $rows ) > 0 ) {
							if( $t_filter )
								$v_start = 40 * (int)($f_itempage_number-1) +1;
							else
								$v_start = 1;
							$v_end   = $v_start + count( $rows ) -1;
						}
			
						echo plugin_lang_get( 'viewing_items_title' );
						echo " ($v_start - $v_end / $t_itembug_count)";
					?>
			
				 </span>
				</td>
<!--					<td class="left" colspan="<?php echo $col_count-2; ?>">-->
			
					</td>
					<?php # -- Page number links -- ?>
					<span class="floatright small"><?php
								$t_filter = gpc_get_int( 'field_id', 0);
								print_page_links( plugin_page( 'view_tablefield_contents_page.php' ).'&field_id='.$t_field_id,1, $t_itempage_count, (int)$f_itempage_number, $t_filter );
							?>
						</span>
					</td>
				</tr>
		<?php # -- Bug list column header row -- ?>
			<tr class="row-category">
			<?php
					foreach( $t_columns as $t_column ) {
						echo '<td>';
			
			//        $bug = bug_format_id($p_bug_id);
			//	print_view_bug_sort_link( lang_get( 'target_version' ), 'target_version', $p_sort, $p_dir, $p_columns_target );
			//	print_sort_icon( $p
			           echo plugin_lang_get( "col_". $t_column);
			          
			       
				 
				echo '</td>';
							
			//			$t_title_function = 'print_column_title';
			//			helper_call_custom_function( $t_title_function, array( $t_column ) );
					}
			
			echo '<td>';
			
			echo plugin_lang_get("col_".'bug_id');
			echo '</td>';
			?>
			</tr>
			
			<?php # -- Spacer row -- ?>
			<tr class="spacer">
				<td colspan="<?php echo $col_count; ?>"></td>
			</tr>
			<?php
				function write_bug_rows ( $p_rows )
				{
					global $t_columns, $t_filter;
			
					$t_in_stickies = ( $t_filter && ( 'on' == $t_filter['sticky_issues'] ) );
			
					# -- Loop over bug rows --
			
					$t_rows = count( $p_rows );
					for( $i=0; $i < $t_rows; $i++ ) {
						$t_row = $p_rows[$i];
			
						# choose color based on status
						
						printf( '<tr %s border="1" valign="top">', helper_alternate_class() );
						
			
						foreach( $t_columns as $t_column ) {
							$t_column_value_function = 'print_column_value';
                           	$t_bug_id =$t_row['bug_id'];
                           	$t_bug_row_id= bug_format_id("$t_bug_id");
							TableFieldApi::print_column_value( $t_column, $t_row);
						} 						
        				
						echo '<td><a href ="view.php?id='. "$t_bug_id" .'">'.$t_bug_row_id.'</a>'.'</td>';
						echo '</tr>';
					}
				}
			
			
				write_bug_rows($rows);
				# -- ====================== end of BUG LIST ========================= --
		//@todo this print the columns 1-50 per preview.
			?>
				<td class="left" colspan="<?php echo $col_count-2; ?>">
			
					</td>
					<?php # -- Page number links -- ?>
					<td class="right" colspan="2">
						<span class="small">
							<?php
								$t_filter	= gpc_get_int( 'filter', 0);
								print_page_links( plugin_page( 'view_tablefield_contents_page.php').'&field_id='. $t_field_id, 1,$t_itempage_count, (int)$f_itempage_number, $t_filter);
							?>
						</span>
					</td>
				</tr>
				<?php #-- End of bug list --?>
			</table>
			</form>

