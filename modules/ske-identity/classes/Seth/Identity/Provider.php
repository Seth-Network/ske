<?php

/**
 * This class is used as a implementation of an Identity Provider to
 * use SKE without an extra module 
 *
 * @author eth4n
 *
 */
class Seth_Identity_Provider extends Identity_Provider {
	
	// ************************************************************************
	//	C O N S T S
	// ************************************************************************
	// SESSION keys
	const _LOGGED_IN		= "logged_in";
	
	// ************************************************************************
	//	A T T S
	// ************************************************************************
	/**
	 * Current identity
	 *
	 * @var Identity_User
	 */
	protected $identity = null;
	
	/**
	 * Configuration array
	 *
	 * @var Array(String=>mixed)
	 */
	protected $config = array();


	/**
	 * Returns a string designed to serve as session key which should
	 * be unique for this running application instance (for multi
	 * SKE systems on one host). Parameter $key is a private constant
	 * showing the usage, e.g. username, logged_in etc
	 *
	 * @param String $key
	 * @return String
	 */
	protected static function _gen_session_key($key) {
		return "seth_".$key;
	}

	/**
	 * Returns the TCP/IP address of the connected agent
	 */
	public static function get_tcpip() {
		$ip = '0.0.0.0';
		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		} else {
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}
	


	// ************************************************************************
	//	M E T H O D S (Identity_Provider)
	// ************************************************************************
	
	public function __construct(array $config=array()) {
		$this->config = $config;
	}
	
	/**
	 * Logs in with credential $username and $password. Returns the Identity_User if login was
	 * successfully or NULL (or an exception), if login failed. If $forceLogoff is set to TRUE
	 * and identity with username $username is already logged in, a logoff is triggered and
	 * then a relogin will be performed
	 *
	 * @param String $username
	 * @param String $password
	 * @param boolean $forceLogoff
	 * @return Identity_User
	 * @throws
	 */
	public function login($username, $password, $forceLogoff=true) {
		// Get the session instance
		$session = Session::instance();
		
		if ( $this->logged_in() && $forceLogoff === TRUE ) {
			$this->logoff($this->current_identity());
		}

		if ( $username == $this->config['admin_user'] && $password == $this->config['admin_passwd'] && $this->config['admin_passwd'] !== "" ) {
			$session->set(self::_gen_session_key(self::_LOGGED_IN), true);
			return $this->current_identity();
		} else {
			throw new Kohana_Exception("Username or Password wrong!");
		}
	}


	/**
	 * Logs off an identity and returns TRUE if logoff was successfully
	 * and FALSE if an error occurs.
	 *
	 * @param Identity $identity
	 */
	public function logoff(Identity $identity, $force=true) {

		// Get the session instance
		$session = Session::instance();

		if ( $this->logged_in() || $force === true) {
			$session->set(self::_gen_session_key(self::_LOGGED_IN), false);
			$this->identity = null;
		} else {
			throw new Kohana_Exception("User not logged in!");
		}
	}
	
	/**
	 * Returns TRUE if a current user is logged in. Using current_identity() to check if a user is
	 * logged in or not may not be sufficent as the method may return a guest's identity.
	 *
	 * @return boolean
	 */
	public function logged_in() {
		$session = Session::instance();
		return ( $session->get(self::_gen_session_key(self::_LOGGED_IN), false) === true );
	}

	/**
	 *
	 *
	 * @return Identity
	 */
	public function current_identity() {
		
		if ( isset($this->identity)) {
			return $this->identity;
		} else {
			if ( $this->logged_in() ) {
				$identity = new Seth_Identity_User(Identity_User::ROOT, $this->config['admin_user']);
				$identity->add_group(new Seth_Identity_Group(Identity_Group::REGISTERED));
				$identity->add_group(new Seth_Identity_Group(Identity_Group::ADMIN));
			} else {
				$identity = new Seth_Identity_User(Identity_User::GUEST);
				$identity->add_group(new Seth_Identity_Group(Identity_Group::UNREGISTERED));
			}

			$identity->add_group(new Seth_Identity_Group(Identity_Group::ALL));

			$this->identity = $identity;
		}
		return $identity;
	}

	/**
	 *
	 *
	 * @return Identity
	 */
	public function get_identity($id) {
		
		if ( strtolower($this->config['admin_user']) == strtolower($id) ) {
			$id = Identity_User::ROOT;
		} else if ( strtolower($id) == "guest") {
			$id = Identity_User::GUEST;
		} 
			$identity = new Seth_Identity_User($id, (($id == Identity_User::ROOT) ? $this->config['admin_user']:"n/a"));
			if ($id == Identity_User::ROOT) {
				$identity->add_group(new Seth_Identity_Group(Identity_Group::REGISTERED));
				$identity->add_group(new Seth_Identity_Group(Identity_Group::ADMIN));
			} else {
				#	$identity = new Seth_Identity_User(Seth::GUEST);
			}
			$identity->add_group(new Seth_Identity_Group(Identity_Group::ALL));
		
		return $identity;
	}
	
	/**
	 *
	 *
	 * @return Identity_Group
	 */
	public function get_group($id) {
		if ( strtolower($id) == "creator") {
			$id = Identity_Group::CREATOR;
		} else if ( strtolower($id) == "author") {
			$id = Identity_Group::AUTHOR;
		} else if ( strtolower($id) == "admin") {
			$id = Identity_Group::ADMIN;
		} else if ( strtolower($id) == "unregistered") {
			$id = Identity_Group::UNREGISTERED;
		} else if ( strtolower($id) == "registered") {
			$id = Identity_Group::REGISTERED;
		}
		
		$identity = new Seth_Identity_Group($id);
		return $identity;
	}
}