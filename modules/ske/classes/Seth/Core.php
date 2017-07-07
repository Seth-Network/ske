<?php defined('SYSPATH') or die('No direct script access.');

class Seth_Core extends Kohana_Core {
	const CFG_DAEMONS = "ske_daemons";
	const CFG_ROUTES_DISABLED = "routes";

	const CACHE_LIFETIME = 3600;
	const CACHE_ROUTES = "ske.routes";
	const CACHE_KEYS = "ske.cache_keys";
	const CACHE_EVENTS = "ske.events";
	const CACHE_EVENT_LISTENERS = "ske.listeners";
	const CACHE_MISSING_LANG_MSG = "ske.l18n_missing";

	/* */
	const DIRECTORY_SEPARATOR = '/';

	/**
	 * Global event bus
	 *
	 * @var Event_Bus
	 */
	public static $event_bus = NULL;

	/**
	 * Dependency injection container
	 *
	 * @var DI_Container
	 */
	public static $di = NULL;

	/**
	 * @override Kohana_Core::init()
	 */
	public static function init(array $settings = NULL) {

		// Fix PHP behaviour when incoming data keys using dots and other characters not valid for variables
		self::fix($_POST, file_get_contents('php://input'));
		if ( isset($_SERVER['QUERY_STRING']) ) {
			self::fix($_GET, $_SERVER['QUERY_STRING']);
	}
		if ( isset($_SERVER['HTTP_COOKIE'])) {
			self::fix($_COOKIE, $_SERVER['HTTP_COOKIE']);
		}

		self::$_paths[] = dirname(__FILE__) ."/../../";
		parent::init($settings);
		self::$event_bus = new Event_Bus();

		/**
		 * Dependency Injection and annotations:
		 */
		$af = new Seth_Annotation_Factory(new Seth_Annotation_Cache());
		// custom registration of Variable annotation to create mapping: @var -> Variable::class
		$af->register_annotation(Default_Value::class);
		$af->register_annotation(Variable::class);
		$af->register_annotation(Returns::class);

		// create DI container
		self::$di = new Seth_DI_Container($af);
	}

	/**
	 * This method will fix PHP's behaviour when handling incoming data for GET, POST and COOKIE. As old PHP's
	 * function to provide all incoming data as variables, special characters (e.g. the dot) will be converted
	 * to an underscore. This method will undo the automaticly replacement.
	 *
	 * All credits belong to Rok Kralj from stackoverflow:
	 * http://stackoverflow.com/users/924109/rok-kralj
	 * Thread handling this problem:
	 * http://stackoverflow.com/questions/68651/get-php-to-stop-replacing-characters-in-get-or-post-arrays
	 *
	 * @param Array $target
	 * @param String $source
	 * @param boolean $discard
	 * @return void
	 */
	private static function fix(&$target, $source, $discard = true) {
		if ($discard)
			$target = array();

		# FIXME remove function when removing older PHP version from list of supported version
		// to make this code work in PHP < 5.3. we can not use anonymous functions, so declare this function correclty
		if ( !function_exists("Seth_Core_fix_function")) {
			function Seth_Core_fix_function ($key) {
				return bin2hex(urldecode($key[0]));
			}
		}

		# FIXME remove function when removing older PHP version from list of supported version
		if ( !function_exists( 'hex2bin' ) ) {
			function hex2bin( $str ) {
				$sbin = "";
				$len = strlen( $str );
				for ( $i = 0; $i < $len; $i += 2 ) {
					$sbin .= pack( "H*", substr( $str, $i, 2 ) );
				}

				return $sbin;
			}
		}

		$source = preg_replace_callback(
				'/(^|(?<=&))[^=[]+/',
				"Seth_Core_fix_function",
				$source
		);

		parse_str($source, $post);
		foreach($post as $key => $val)
			$target[ hex2bin($key) ] = $val;
	}
}
