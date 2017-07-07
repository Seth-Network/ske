<?php


abstract class Identity_Provider {

	
	/**
	 * Returns the Identity_Provider instance of given class. If no class is given, then the SKE's default
	 * identity provider will be returned. The provider's configuration may be adapted using the
	 * second parameter to overwrite any predefined configurations
	 *
	 * @param String $class
	 * @param Array(String=>mixed) $config
	 * @return Identity_Provider
	 */
	public static function factory($class=NULL, array $config=array()) {
		if ( $class === NULL ) {
			$class = Kohana::$config->load("ske")->get("identity_provider", null);
		}
		
		if ( $class === NULL ) {
			throw new Exception("Can not load default identity provider: No default configuration available!");
		}
		
		try {
			$config = array_merge($config, Kohana::$config->load("ske_identity_management")->get($class, array()));
			$provider = new $class($config);
		} catch ( Exception $e ) {
			throw new Exception("Exception while loading identity provider '$class': ". $e->__toString());
		}
		return $provider;
	}
	
	/**
	 * Returns the TCP/IP address of the connected agent
	 * 
	 * @return String
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
	
	/**
	 * Invokes a Request_Event for a user identity with given identifier. Returns the identity if one
	 * was found or NULL, if no identity is available for the identifier
	 * 
	 * @param String $identifier
	 * @return Identity_User
	 */
	public static function request_user($identifier) {
		return Kohana::$event_bus->post(new Identity_Request_User_Event($identifier))->identity();
	}
	
	/**
	 * Invokes a Request_Event for a group identity with given identifier. Returns the identity if one
	 * was found or NULL, if no identity is available for the identifier
	 *
	 * @param String $identifier
	 * @return Identity_Group
	 */
	public static function request_group($identifier) {
		return Kohana::$event_bus->post(new Identity_Request_Group_Event($identifier))->identity();
	}
	
	public function __construct(array $config=array()) {
	
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
	 */
	public abstract function login($username, $password, $forceLogoff=true);

	/**
	 * Logs off an identity and returns TRUE if logoff was successfully
	 * and FALSE if an error occurs.
	 *
	 * @param Identity $identity
	 */
	public abstract function logoff(Identity $identity, $force=true);

	/**
	 * Returns the identity of the current active user. If the user is not logged in, a
	 * guest identity is returned
	 *
	 * @return Identity_User
	 */
	public abstract function current_identity();
	
	/**
	 * Returns the identity object for a user with given identifier. The identitfier may be of type integer as a
	 * numeric ID or a username. If no user is found, NULL is
	 * returned
	 *
	 * @param String $id
	 * @return Identity_User
	 */
	public abstract function get_identity($id);
	
	/**
	 * Returns the identity object for a group with given id. The identitfier may be of type integer as a
	 * numeric ID or a groupname. If no group is found, NULL is
	 * returned
	 *
	 * @param String $id
	 * @return Identity_Group
	 */
	public abstract function get_group($id);
	
	/**
	 * Returns TRUE if a current user is logged in. Using current_identity() to check if a user is
	 * logged in or not may not be sufficent as the method may return a guest's identity.
	 * 
	 * @return boolean
	 */
	public function logged_in() {
		return ( ( $tmp = $this->current_identity()) != null && $tmp->id() != Identity_User::GUEST );
	}
	
	/**
	* Returns TRUE if the current logged in user is the root user. The detailed identification of
	* a root user may depend on the provider's implementation
	*
	* @return boolean
	*/
	public function is_root() {
		return ( $this->current_identity()->id() == Identity_User::ROOT || $this->current_identity()->id() == Identity_User::SYSTEM );
	}

}