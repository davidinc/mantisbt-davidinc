<?php


class TerminologyPlugin extends MantisPlugin {
	function register() {
		$this->name = 'Terminology';    # Proper name of plugin
		$this->description = 'Allows to customized terminology on per project base. You need to edit lang/strings_english.txt in the plugin folder!';    # Short description of the plugin
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
			'EVENT_LANG_GET' => 'lang_get',
		    
		);
	}
	
	function lang_get( $p_event, $p_translation, $p_string, $t_lang) {
		
/*
 * configures the terminology prefixes for the different projects
  ____ ___  _   _ _____ ___ ____ _   _ ____      _  _____ ___ ___  _   _ 
 / ___/ _ \| \ | |  ___|_ _/ ___| | | |  _ \    / \|_   _|_ _/ _ \| \ | |
| |  | | | |  \| | |_   | | |  _| | | | |_) |  / _ \ | |  | | | | |  \| |
| |__| |_| | |\  |  _|  | | |_| | |_| |  _ <  / ___ \| |  | | |_| | |\  |
 \____\___/|_| \_|_|   |___\____|\___/|_| \_\/_/   \_\_| |___\___/|_| \_|

 */
		$config_TerminologyPlugin_projects = array(49 => "SR", 50 => "PR", 55 => "PR", 1 =>"ICT");
/** END OF THE CONFIGURATION :-) **/
		
		
		# following lines are important.
		# otherwise mantis will crash when the user is not logged in and/or the cookie became invalid
		if ( auth_is_user_authenticated() )
			# works only proplery when user is logged in
			$project = helper_get_current_project();
		else
			$project = null;
			
		# 1. find out all parents of $project
		$parents = project_hierarchy_inheritance( $project );
		
		# 2. check if 1 is in that list of parents
		#  if yes, set $prefix to ICT_
		$prefix = null;
		foreach($config_TerminologyPlugin_projects as $id => $code) {
			if (in_array($id , $parents)) {
				$prefix = $code . "_";
				break;
			}
		}
			
		if ( is_null( $prefix ) ) 
			return $p_translation;
		
				
		$t_basename = plugin_get_current();
		lang_load( $t_lang, config_get( 'plugin_path' ) . $t_basename . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );
				
//		$string_overwrite = lang_exists("plugin_Terminology_SR_report_bug_link", $t_lang);
//		var_dump($string_overwrite);
//		echo "xxx".$string_overwrite."xxx";
//				die();
		
//		echo lang_get("plugin_Terminology_SR_report_bug_link");
		$full_string = 'plugin_' . $t_basename . "_" . $prefix . $p_string;
		
		if ( lang_exists($full_string, $t_lang) )
			return plugin_lang_get($prefix . $p_string);
		else
			return $p_translation;
	}
}
