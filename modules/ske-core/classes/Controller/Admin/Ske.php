<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Ske extends Controller {
	
	// auto render template
	protected $auto_render = TRUE;
	// current view
	protected $view = null;
	
	// ************************************************************************
	//	M E T H O D S
	// ************************************************************************
	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before() {
		parent::before();
		
		$this->view = Smarty_View::factory(NULL);
		
	}
	
	
	
	
	/**
	 * Assigns the view [View] as the request response if it is set.
	 */
	public function after() {
	
		if ($this->auto_render === TRUE && $this->view != null) {
			$this->view->request = $this->request;
	
	
			$this->view->versions = array(
					'smarty' => str_replace('Smarty-', '', Smarty::SMARTY_VERSION),
					'kohana' => Kohana::VERSION . ' ' . Kohana::CODENAME,
					'module' => Smarty_View::VERSION,
					'php'    => phpversion(),
					'server' => arr::get($_SERVER, 'SERVER_SOFTWARE'),
			);
	
			$this->response->body($this->view->render());
		}
	
		parent::after();
	}
	
	/**
	 * Returns the current used template package name or
	 * 'default' if none is available
	 *
	 * @return String
	 */
	public static function getTemplate() {
		return 'admin';
	}
	
	/**
	 * Sets and gets the controller's view
	 *
	 * @param   View   $value  Controller's view
	 * @return  SCMS
	 */
	public function view($value=null) {
		if ( $value === null ) {
			return $this->view;
		}
		$this->view = $value;
		return $this;
	}

	
	public function action_settings() {
		
		
		$this->auto_render = false;
		$this->response->body(SACP::render_config(array(
				"SKE" => 'ske',
				"Kohana" => 'env',
				"Modules" => "modules",
				"Database" => 'database',
				
				"Session" => 'session',
				"Caching" => 'cache',
				
				"Ident" => 'ske_identity_management',
				"Captcha" => 'captcha',
				), 
				'Settings', null, array(

						'modules' => new Multiframe_Table()
		)));
	}
	
	public function action_env() {
		$env = new Multiframe_Value_Status("");
		
		switch (Kohana::$environment) {
			case Kohana::PRODUCTION :
				$env->label("Production")->color(Multiframe_Value_Status::GREEN);
				break;
			case Kohana::STAGING :
				$env->label("Staging")->color(Multiframe_Value_Status::BLUE);
				break;
			case Kohana::TESTING :
				$env->label("Testing")->color(Multiframe_Value_Status::YELLOW);
				break;
			case Kohana::DEVELOPMENT :
				$env->label("Development")->color(Multiframe_Value_Status::RED);
				break;
			default :
				$env->label("n/a")->color(Multiframe_Value_Status::YELLOW);
		}

		
		$this->view->kohana = new Multiframe_Config("",array(
				new Multiframe_Labelled_Value("", _("Server"), Kohana::$server_name),
				new Multiframe_Labelled_Value("", _("Version"), Kohana::VERSION),
				new Multiframe_Labelled_Value("", _("Codename"), Kohana::CODENAME),
				new Multiframe_Labelled_Value("", _("Environment"), $env),
				new Multiframe_Labelled_Value("", _("Base URL"), Kohana::$base_url),
				new Multiframe_Labelled_Value("", _("Charset"), Kohana::$charset),
				new Multiframe_Labelled_Value("", _("Timezone"), Kohana::$config->load('env')->get('timezone')),
				new Multiframe_Labelled_Value("", _("Content type"), Kohana::$content_type),
				new Multiframe_Labelled_Value("", _("Profiling"), with(new Multiframe_Value_Boolean("", Kohana::$profiling))->style(Multiframe_Value_Boolean::YES_NO)),
				new Multiframe_Labelled_Value("", _("Show errors"), with(new Multiframe_Value_Boolean("", Kohana::$errors))->style(Multiframe_Value_Boolean::YES_NO)),
				new Multiframe_Labelled_Value("", _("Log errors"), with(new Multiframe_Value_Boolean("", Kohana::$log_errors))->style(Multiframe_Value_Boolean::YES_NO)),
				new Multiframe_Labelled_Value("", _("Log directory"), with(new Multiframe_Link())
						->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'logs'))))
						->label(new Multiframe_Value_String("", SACP::clearPaths(Kohana::$config->load('env')->get('log_dir', ''))))),
				new Multiframe_Labelled_Value("", _("Safe mode"), with(new Multiframe_Value_Boolean("", Kohana::$safe_mode))->style(Multiframe_Value_Boolean::YES_NO)),
				new Multiframe_Labelled_Value("", _("Caching"), with(new Multiframe_Value_Boolean("", Kohana::$caching))->style(Multiframe_Value_Boolean::YES_NO)),
				new Multiframe_Labelled_Value("", _("Cache directory"), SACP::clearPaths(Kohana::$cache_dir)),
				new Multiframe_Labelled_Value("", _("Language"), Kohana::$config->load('env')->get('language')),
				new Multiframe_Labelled_Value("", _("Enable default route"), with(new Multiframe_Value_Boolean("", Kohana::$config->load('env')->get('enable_default_route')))->style(Multiframe_Value_Boolean::YES_NO)),

		));
		
		$this->view->paths = new Multiframe_Config("",array(
				new Multiframe_Labelled_Value("", "DOCROOT", SACP::localPath("DOCROOT/")),
				new Multiframe_Labelled_Value("", "APPPATH", SACP::localPath("APPPATH/")),
				new Multiframe_Labelled_Value("", "MODPATH", SACP::localPath("MODPATH/")),
				new Multiframe_Labelled_Value("", "ASSETSPATH", SACP::localPath("ASSETSPATH/")),
				new Multiframe_Labelled_Value("", "SYSPATH", SACP::localPath("SYSPATH/")),
		));
		
		$this->view->ske = new Multiframe_Config("",array(
				new Multiframe_Labelled_Value("", _("Directory separator"), Kohana::DIRECTORY_SEPARATOR),
		));
		
		$phpinfo = self::phpinfo_array(true);
		
		$entities = array();
		foreach ( $phpinfo as $section => $values ) {
			$section = new Multiframe_Config_Section($section);
			foreach ( $values as $var_name => $var_value ) {
				$var_value = ( is_array($var_value) ) ? implode(", ", $var_value):$var_value;
				$section->add_entity(new Multiframe_Labelled_Value("", HTML::chars($var_name), SACP::clearPaths($var_value)));
			}
			$entities[] = $section;
		}
		
		$this->view->phpinfo = new Multiframe_Config("", $entities);
		
		

		$this->view->set_filename($this->getTemplate() .'/sacp_environment.tpl');
	}
	
	public function action_modules() {

			 $modules = $activeModules = Kohana::modules();
			 $dir = new DirectoryIterator(MODPATH);
			foreach ($dir as $file){
				// Get the file name
				$filename = $file->getFilename();
				if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~') {
					// Skip all hidden files and UNIX backup files
					continue;
				}
				if ($file->isDir() && array_search($file->getPathName(), $activeModules) === false ) {
					$modules[$file->getBasename()] = $file->getPathName();
				}
			}
			
		// display all
		if ( $this->request->param('id', null) === null ) {
			$converter = new Module_Tbl_Converter();
			ksort($modules);
			
			Module_Tbl_Converter::$active = array_keys($activeModules);
			
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('active', 'Active', null, $converter),
					new Multiframe_Table_Column('status', 'Status', null, $converter),
					new Multiframe_Table_Column('name', 'Module', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter),
			), $modules);
			
			
			$this->view->table = $table;
			$this->view->title = 'Modules';
			
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		} else {
			$module = $this->request->param('id');
			# check if module name exists
			if ( !isset($modules[$module])) {
				throw new Exception("Unknown module ". $module);
			}
			
			$this->view->module = $module;
			$this->view->back_link = URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'modules')));
			
			$this->view->info = new Multiframe_Config("",array(
				new Multiframe_Labelled_Value("", _("Module"), $module),
				new Multiframe_Labelled_Value("", _("Active"), with(new Multiframe_Value_Boolean("", isset($activeModules[$module])))->style(Multiframe_Value_Boolean::ENABLE_DISABLE)),
				new Multiframe_Labelled_Value("", _("Path"), SACP::clearPaths($modules[$module])),
				new Multiframe_Labelled_Value("", _("Size"), SACP::human_filesize(self::dirSize($modules[$module]))),
				new Multiframe_Labelled_Value("", _("Status"), with(new Multiframe_Value_Status("", Multiframe_Value_Status::WARNING))->label("n/a")),
			));
			
			// ######################################################################################
			$converter = new Route_Converter();
			$routes = Cache::instance()->get(SKE::CACHE_ROUTES, array());
			ksort($routes);
			foreach ($routes as $k => $route_defined_in ) {
				$path = SACP::clearPaths($route_defined_in);
				$route_module = "";
				if ( ( $tmp = strpos($path, 'MODPATH/')) !== false ) {
					$route_module = substr($path, ($tmp + strlen('MODPATH/')));
					$route_module = substr($route_module, 0, strpos($route_module, '/'));
				}
				
				if ( $route_module != $module ) {
					unset($routes[$k]);
				}
			}
			
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('status', 'Active', null, $converter),
					new Multiframe_Table_Column('route', 'Route', null, $converter),
					new Multiframe_Table_Column('defined_in', 'Defined in', null, $converter),
					new Multiframe_Table_Column('url', 'URL', null, $converter),
			), $routes);
			
			$this->view->routes = $table;
			
			// ######################################################################################
			$converter = new Config_Converter();
			$config_files = SACP::scan_files($modules[$module] . Kohana::DIRECTORY_SEPARATOR .'config');
						
			foreach ( $config_files as $config ) {
				// get config file name with subfolders within the module config's folder
				$config = substr(SACP::localPath($config), (strpos($config, $module . Kohana::DIRECTORY_SEPARATOR .'config' . Kohana::DIRECTORY_SEPARATOR) + strlen($module . Kohana::DIRECTORY_SEPARATOR .'config' . Kohana::DIRECTORY_SEPARATOR)));
			
				if ( file_exists(APPPATH .'config' . Kohana::DIRECTORY_SEPARATOR .$config) ) {
					$config_files[] = APPPATH .'config' . Kohana::DIRECTORY_SEPARATOR .$config;
				}
			}

				
			// sort by config node name
			function SKE_action_settings_config_sort($a, $b) {
				return strcmp(basename($a), basename($b));
			}
			usort($config_files, 'SKE_action_settings_config_sort');
			
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('node', 'Node', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter),
			), $config_files);
				
			$this->view->configs = $table;
			
			// ######################################################################################
			$converter = new Events_Converter();
			$events = Cache::instance()->get(SKE::CACHE_EVENTS, array());
			ksort($events);
			
			foreach ($events as $event => $event_data ) {
				foreach ( $event_data[1] as $c => $event_called_in ) {
					$path = SACP::clearPaths($event_called_in);
					$event_module = "";
					if ( ( $tmp = strpos($path, 'MODPATH/')) !== false ) {
						$event_module = substr($path, ($tmp + strlen('MODPATH/')));
						$event_module = substr($event_module, 0, strpos($event_module, '/'));
					}
										
					if ( $event_module == $module ) {
						$event_data[3] = $c;
						continue 2;
					}
				}	
				
				unset($events[$event]);
			}
			
			$events_tbl = array();
			foreach ( $events as $c => $e ) {
				$events_tbl[] = array($c=>$e);
			}
				
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('event', 'Event', null, $converter),
					new Multiframe_Table_Column('posted_by', 'Posted by', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter)
			), $events_tbl);
				
			$this->view->events = $table;
			
			// ######################################################################################
				$converter = new Listeners_Converter();
				$listeners = Cache::instance()->get(SKE::CACHE_EVENT_LISTENERS, array());
				ksort($listeners);
					
				foreach ($listeners as $listener => $listener_data ) {
						$path = SACP::clearPaths($listener_data[3]);
						$listener_module = "";
						if ( ( $tmp = strpos($path, 'MODPATH/')) !== false ) {
							$listener_module = substr($path, ($tmp + strlen('MODPATH/')));
							$listener_module = substr($listener_module, 0, strpos($listener_module, '/'));
						}
							
						if ( $listener_module != $module ) {
							unset($listeners[$listener]);
							break;
						}
					}
	
					
				$listeners_tbl = array();
				foreach ( $listeners as $c => $e ) {
					$listeners_tbl[] = array($c=>$e);
				}
				
				// define table
				$table = new Multiframe_Table(null, array(
						new Multiframe_Table_Column('id', '#', null, $converter),
						new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
						new Multiframe_Table_Column('name', 'Listener', null, $converter),
						new Multiframe_Table_Column('event', 'Event', null, $converter)
				), $listeners_tbl);
				
				$this->view->listeners = $table;
			
			// ######################################################################################
			$converter = new Vendor_Converter();
			$libs = SACP::list_folders('vendor', array($modules[$module].Kohana::DIRECTORY_SEPARATOR), array(
					'/.+\/vendor\/[^\/]+\/.+/',
			));
				
			// sort by config node name
			function SKE_action_settings_lib_sort($a, $b) {
				return strcmp(basename($a), basename($b));
			}
			usort($libs, 'SKE_action_settings_lib_sort');
			
			
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('lib', 'Library', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter)
			), $libs);
			
			$this->view->libs = $table;
			// ######################################################################################
			
			$this->view->set_filename($this->getTemplate() .'/sacp_module.tpl');
		}

	}
	
	public function action_configurations() {
		// display all configuration files
		if ( $this->request->param('id', null) === null ) {
			$converter = new Config_Converter();
			
			// load configuration files
			$config_files = SACP::list_files('config', null, array(
				'/.*\/config\/devtools\/.*/',
				'/.*\/config\/sdf\/.*/',
			));
			
			// sort by config node name
			function SKE_action_settings_config_sort($a, $b) {
				return strcmp(basename($a), basename($b));
			}
			usort($config_files, 'SKE_action_settings_config_sort');

			// display table		
			$this->auto_render = false;
			$this->response->body(SACP::render_table(new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('node', 'Node', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter),
			), $config_files), $this->request, array(
				'title' => _('Configurations'),
				'actions' => array(
					'purge' => array('Purge', 'javascript:')
				)
			)));
		}
		// display single configuration file
		else {
			$this->auto_render = false;
			
			$file = base64_decode($this->request->param('id'));
			$local_path = SACP::localPath($file);
			
			$info = pathinfo($local_path);
			
			// check if given file is within the configuration directory
			$node = str_replace('.' . $info['extension'], '', substr($local_path, (strpos($local_path, 'config' . Kohana::DIRECTORY_SEPARATOR) + 7)));
			
			$found = Kohana::find_file('config', $node, "php");
			if (is_array($found) && empty($found)) {
				$pattern = '/MODPATH\/([^\/]+)\/config\/([^.]+)/';
				$activeModules = array_keys(Kohana::modules());
				// check if - even when Kohana cant find the file - the file has a valid directory structure within the modpath (to prevent hacks)
				// the selected module is disabled (enabled module's configurations will be found) and that the file exists
				if ( preg_match($pattern, $file, $matches) == 0 || array_search($matches[1], $activeModules) !== false || !file_exists($local_path)) {
					throw new Kohana_Exception("Given file is not a valid configuration file!");
				} else {
					$node = $matches[2];
					SACP::add_warning_msg("The module this configuration belongs to seems to be disabled: This configuration may not have any effect until you enable the module!");
				}
			}
			// check if this configuration node will be merged or overwritten by other configurations
			else if (is_array($found) && count($found) > 1) {
				SACP::add_warning_msg('This configuration node may be merged or overwritten by one or more configuration files with the same node name!');
			}
			
			$this->response->body(SACP::render_file($local_path, $this->request, array(
					'title' => $node,
					'title_sub' => $file,
					'display_raw' => true,
					'edit_raw' => true,
					'cfg_node' => $node,
					'tab_content' => _('Configuration'),
					'content_render' => new Config_Renderer(),
					'back_link' => URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'configurations')))
			)));
		}
	}
	public function action_events() {
		// display all configuration files
		if ( $this->request->param('id', null) === null ) {
			$converter = new Events_Converter();
				
			// load configuration files
			$events = Cache::instance()->get(SKE::CACHE_EVENTS, array());
			ksort($events);
			$events_tbl = array();
			foreach ( $events as $c => $e ) {
				$events_tbl[] = array($c=>$e);
			}
			
	
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('event', 'Event', null, $converter),
					new Multiframe_Table_Column('listeners', 'Listeners', null, $converter),
					new Multiframe_Table_Column('poster', 'Poster', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter)
			), $events_tbl);
	
			$this->view->table = $table;
			$this->view->title = 'Events';
	
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		}
		// display single configuration file
		else {
			
		}
	}
	
	public function action_listeners() {
		$converter = new Listeners_Converter();
		$listeners = Cache::instance()->get(SKE::CACHE_EVENT_LISTENERS, array());
		ksort($listeners);

		$listeners_tbl = array();
		foreach ( $listeners as $c => $e ) {
			$listeners_tbl[] = array($c=>$e);
		}
		
		// define table
		$table = new Multiframe_Table(null, array(
				new Multiframe_Table_Column('id', '#', null, $converter),
				new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
				new Multiframe_Table_Column('name', 'Listener', null, $converter),
				new Multiframe_Table_Column('method', 'Method', null, $converter),
				new Multiframe_Table_Column('event', 'Event', null, $converter),
				new Multiframe_Table_Column('path', 'Path', null, $converter),
				new Multiframe_Table_Column('priority', '%', null, $converter),
		), $listeners_tbl);
	
			$this->view->table = $table;
			$this->view->title = 'Listeners';
	
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		
	}
	
	public function action_vendor() {
		// display all configuration files
		if ( $this->request->param('id', null) === null ) {
			$converter = new Vendor_Converter();
	
			// load configuration files
			$libs = SACP::list_folders('vendor', null, array(
					'/.*\/vendor\/[^\/]+\/.+/',
			));
							
			// sort by config node name
			function SKE_action_settings_lib_sort($a, $b) {
				return strcmp(basename($a), basename($b));
			}
			usort($libs, 'SKE_action_settings_lib_sort');
				
	
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('lib', 'Library', null, $converter),
					new Multiframe_Table_Column('path', 'Path', null, $converter)
			), $libs);
	
			$this->view->table = $table;
			$this->view->title = 'Libraries';
	
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		}
		
		else {
				
		}
	}
	
	public function action_routes() {
		// display all configuration files
		if ( $this->request->param('id', null) === null ) {
			$converter = new Route_Converter();
	
			// load configuration files
			$routes = Cache::instance()->get(SKE::CACHE_ROUTES, array());
				
			if ( empty($routes) ) {
				foreach ( Route::all() as $r ) {
					$routes[Route::name($r)] = "#". ( $r instanceof Seth_Route ? $r->pattern():"");
				}
				SACP::add_warning_msg("It looks like the SKE module not isn't installed correctly!");
			}
				
			ksort($routes);
	
			// define table
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('status', 'Active', null, $converter),
					new Multiframe_Table_Column('route', 'Route', null, $converter),
					new Multiframe_Table_Column('defined_by', 'Defined by', null, $converter),
					new Multiframe_Table_Column('url', 'URL', null, $converter),
			), $routes);
	
	
			$this->view->table = $table;
			$this->view->title = 'Routes';
	
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		}
		// display single configuration file
		else {
				
		}
	}
	
	
	
	public function action_logs() {
		// display all log files
		if ( $this->request->param('id', null) === null ) {
			$log_files = array();
			$folder = Kohana::$config->load('env')->get('log_dir');
			
			
			$converter = new Log_Converter();
		
			if ( $folder !== NULL ) {
				$log_files = SACP::scan_files($folder);
				$log_files = array_reverse($log_files);
			}
		
			$table = new Multiframe_Table(null, array(
					new Multiframe_Table_Column('id', '#', null, $converter),
					new Multiframe_Table_Column('check', new Multiframe_Input_Boolean("", "", false)),
					new Multiframe_Table_Column('date', 'Date', null, $converter),
					new Multiframe_Table_Column('number', 'Errors', null, $converter),
					new Multiframe_Table_Column('data', 'First error', null, $converter),
			), $log_files);
		
		
			$this->view->table = $table;
			$this->view->title = 'Logs';
		
			$this->view->set_filename($this->getTemplate() .'/sacp_table.tpl');
		}
		// display single log file
		else {
			$this->auto_render = false;
			$file = rtrim(Kohana::$config->load('env')->get('log_dir'), "/\\") .DIRECTORY_SEPARATOR. date("Y/m/d", $this->request->param('id')) .".php";
			$this->response->body(SACP::render_file($file, $this->request, array(
				'title' => _('Log'),
				'title_sub' => SACP::clearPaths($file),
				'back_link' => URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'logs')))
			)));
		}
	}
	
	/**
	 * Get the directory size
	 * @param directory $directory
	 * @return integer
	 */
	private static function dirSize($directory) {
		$size = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
			$size+=$file->getSize();
		}
		return $size;
	}
	
	/**
	 * Fetches the phpinfo() data and splits the data using some regular expressions. The returned
	 * array will be of form:
	 * array(
	 * 	[Section Name] => array(
	 * 		[Variable Name] => [Value]))
	 *
	 * @param Array(String=>Array(String=>String)) $return
	 */
	private static function phpinfo_array($return=false){
		/* Andale!  Andale!  Yee-Hah! */
		ob_start();
		phpinfo(-1);
	
		$pi = preg_replace(
				array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
						'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
						"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
						'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
						.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
						'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
						'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
						"# +#", '#<tr>#', '#</tr>#'),
				array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
						'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
						"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
						'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
						'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
						'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
				ob_get_clean());
	
		$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
		unset($sections[0]);
	
		$pi = array();
		foreach($sections as $section){
			$n = substr($section, 0, strpos($section, '</h2>'));
			preg_match_all(
			'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
			$section, $askapache, PREG_SET_ORDER);
			foreach($askapache as $m)
				if ( !isset($m[2]) ) {
					$pi[$n][$m[1]] = $m[1];
				} else {
				$pi[$n][$m[1]]= ( !isset($m[3]) || $m[2] == $m[3] ) ? $m[2]:array_slice($m,2);
				}
		}
	
		return ($return === false) ? print_r($pi) : $pi;
	}
}

class Config_Renderer extends SACP_File_Renderer {
	/**
	 * Specifies required $file data into the $view in order for the
	 * view to display the file content correctly
	 *
	 * @param String $file
	 * @param Kohana_View $view
	 * @param Array(String=>mixed) $options
	 * @param Request $request
	 */
	public function read($file, Kohana_View $view, array $options, Request $request) {
		$cfg = new Multiframe_Config("Configuration");
		
		$config = include($file);
		
		SACP::_config_transformer($config, $options['cfg_node'], $cfg, array(), array());
		
		$view->content = $cfg;
		return;
	}
}


/**
 * 
 * @author eth4n
 *
 */
class Log_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {
		
		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'date' ) {
			$row_object = ( is_array($row_object) ) ? current($row_object):$row_object;
			$link = new Multiframe_Link();
			$link->label(with(new Multiframe_Value_Date("", filemtime($row_object)))->format(Kohana::$config->load('ske')->get('date_format', 'd.m.Y')));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'logs', 'id' => filemtime($row_object)))));
			return $link;
		} else if ( $col->uid() == 'number' ) {
			$row_object = ( is_array($row_object) ) ? current($row_object):$row_object;
			return new Multiframe_Value_String($col->uid(), (preg_match_all('/^--/mi', file_get_contents($row_object), $matches) + 1) );
		} else if ( $col->uid() == 'data' ) {
			$row_object = ( is_array($row_object) ) ? current($row_object):$row_object;
			$content = file($row_object);
			if ( count($content) >= 3 ) {
				return new Multiframe_Value_String($col->uid(), SACP::clearPaths($content[2]));
			}
			return new Multiframe_Value_String($col->uid(), "n/a");
		}
	}
}

class Config_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {
		
		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'node' ) {
			$config = ( is_array($row_object) ) ? current($row_object):$row_object;
			
			
			$info = pathinfo($config);
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $info['filename']));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'configurations', 'id' => base64_encode(SACP::clearPaths($config))))));
			return $link;
		} else if ( $col->uid() == 'path' ) {
			$config = ( is_array($row_object) ) ? current($row_object):$row_object;
			$info = pathinfo($config);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($info['dirname']));
		}
	}
}

class Listeners_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {

		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'name' ) {
			$listener = key($row_object);
				
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $listener));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'class', 'id' => $listener))));
			return $link;
		} else if ( $col->uid() == 'event' ) {
			$data = current($row_object);
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $data[0]));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'class', 'id' => $data[0]))));
			return $link;
		} else if ( $col->uid() == 'method' ) {
			$data = current($row_object);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($data[1]));
		} else if ( $col->uid() == 'priority' ) {
			$data = current($row_object);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($data[2]));
		} else if ( $col->uid() == 'path' ) {
			$data = current($row_object);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($data[3]));
		}
	}
}
class Vendor_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {

		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'lib' ) {
			$config = ( is_array($row_object) ) ? current($row_object):$row_object;
			
			$info = pathinfo($config);
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $info['filename']));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'vendor', 'id' => base64_encode(SACP::clearPaths($config))))));
			return $link;
		} else if ( $col->uid() == 'path' ) {
			$config = ( is_array($row_object) ) ? current($row_object):$row_object;
			$info = pathinfo($config);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($info['dirname']));
		}
	}
}

class Route_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	/**
	 * 
	 * @var Config_Group
	 */
	public static $config;
	
	public function __construct(Multiframe_Value $defaultValue=NULL) {
		parent::__construct($defaultValue);
		self::$config = Kohana::$config->load(SKE::CFG_ROUTES_DISABLED)->as_array();
	}
	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {

		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} 
		
		else if ( $col->uid() == 'route' ) {
			$route = key($row_object);
			
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $route));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'routes', 'id' => $route))));
			return $link;
		} 
		
		else if ( $col->uid() == 'defined_by' ) {
			$route = key($row_object);
			$data = current($row_object);
			$file = substr($data, 0, strpos($data, '#'));

			$path = SACP::clearPaths($file);
			
			if ( strpos($path, 'APPPATH') !== false ) {
				$definedBy = new Multiframe_Value_String($col->uid(), "Application");
			} else if ( strpos($path, 'MODPATH') !== false ) {
				
				$module = substr($path, (strpos($path, 'MODPATH/') + strlen('MODPATH/')));
				$module = substr($module, 0, strpos($module, '/'));
				
				$definedBy = new Multiframe_Link();
				$definedBy->label(new Multiframe_Value_String($col->uid(), $module));
				$definedBy->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'modules', 'id' => $module))));
			} else if ( strpos($path, 'SYSPATH') !== false ) {
				$definedBy = new Multiframe_Value_String($col->uid(), "System");
			}
			else {
				$definedBy = new Multiframe_Value_String($col->uid(), "Unknown");
			}
			
			return $definedBy;
		}
		
		else if ( $col->uid() == 'defined_in' ) {
			$route = key($row_object);
			$data = current($row_object);
			$file = substr($data, 0, strpos($data, '#'));
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($file));
		}
		else if ( $col->uid() == 'url' ) {
			$route = key($row_object);
			$data = current($row_object);
			$url = HTML::chars(substr($data, strpos($data, '#')+1));
			
			return new Multiframe_Value_String($col->uid(), $url);
		} else if ( $col->uid() == 'status' ) {
			$route = key($row_object);
			
			if ( array_search($route, self::$config) !== false ) {
				return with(new Multiframe_Value_Status("", Multiframe_Value_Status::DANGER))->label("Blocked");
			}
			try {
				$route = Route::get($route);
			} catch ( Exception $e ) {
				return with(new Multiframe_Value_Status("", Multiframe_Value_Status::WARNING))->label("Inactive");
			}
			return with(new Multiframe_Value_Status("", Multiframe_Value_Status::SUCCESS))->label("Active");
		}
	}
}

class Events_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;

	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	*/
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {

		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'event' ) {
			$event = key($row_object);
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $event));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'events', 'id' => $event))));
			return $link;
		} else if ( $col->uid() == 'posted_by' ) {
			$event_data = current($row_object);
			$posted_by = "";
			foreach ( $event_data[1] as $class => $file ) {
				$posted_by .= "$class (". SACP::clearPaths($file) .")\n";
			}
			return new Multiframe_Value_String($col->uid(), nl2br($posted_by));
		} else if ( $col->uid() == 'poster' ) {
			$event_data = current($row_object);
			return new Multiframe_Value_String($col->uid(), "". count($event_data[1]));
		} else if ( $col->uid() == 'listeners' ) {
			$event_data = current($row_object);
			return new Multiframe_Value_String($col->uid(), "". $event_data[0]);
		} else if ( $col->uid() == 'path' ) {
			$event_data = current($row_object);
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths($event_data[2]));
		}
	}
}

class Module_Tbl_Converter extends Multiframe_Table_Column_Converter {
	public static $count = 1;
	
	public static $active = array();

	/**
	 * @see Seth_Multiframe_Table_Column_Converter::value()
	 */
	public function value(Multiframe_Table_Column $col, $row_object, Multiframe_Value $targetValue=NULL) {
		
		if ( $col->uid() == 'id' ) {
			return new Multiframe_Value_String($col->uid(), self::$count++);
		} else if ( $col->uid() == 'name' ) {
			$module = key($row_object);
			$link = new Multiframe_Link();
			$link->label(new Multiframe_Value_String($col->uid(), $module));
			$link->href(URL::site(Route::get('admin_default')->uri(array('controller' => 'ske', 'action' => 'modules', 'id' => $module))));
			return $link;
		} else if ( $col->uid() == 'path' ) {
			return new Multiframe_Value_String($col->uid(), SACP::clearPaths(current($row_object)));
		} else if ( $col->uid() == 'active' ) {
			$module = key($row_object);
			return with(new Multiframe_Value_Boolean($col->uid(), array_search($module, self::$active) !== false))->style(Multiframe_Value_Boolean::ENABLE_DISABLE);
		} else if ( $col->uid() == 'status' ) {
			$module = key($row_object);
			return with(new Multiframe_Value_Status("", Multiframe_Value_Status::WARNING))->label("n/a");
		}
	}
}