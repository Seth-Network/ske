<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This class is used as a implementation of an Identity Provider
 *
 * @author eth4n
 *
 */
class Seth_Identity_Provider extends Identity_Provider
{

    // ************************************************************************
    //	C O N S T S
    // ************************************************************************
    // SESSION keys
    const _LOGGED_IN = "logged_in";

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
    protected static function _gen_session_key($key)
    {
        return "seth_" . $key;
    }


    // ************************************************************************
    //	M E T H O D S (Identity_Provider)
    // ************************************************************************

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    protected function _login($username, $password, $remember) {
        // Get the session instance
        $session = Session::instance();

        if ($this->logged_in()) {
            $this->logoff();
        }

        if ($username == $this->config['admin_user']
            && $password == $this->config['admin_passwd']
            && $this->config['admin_passwd'] != "") {
            $session->set(self::_gen_session_key(self::_LOGGED_IN), true);
            return $this->current_identity();
        } else {
            throw new Kohana_Exception("Username or Password wrong!");
        }
    }

    /**
     * Get the stored password for a username.
     *
     * @param   mixed   $username  Username
     * @return  string
     */
    public function password($username) {

    }

    /**
     * Compare password with original (plain text). Works for current (logged in) user
     *
     * @param   string   $password  Password
     * @return  boolean
     */
    public function check_password($password) {

    }

    /**
     * Returns TRUE if a current user is logged in. Using current_identity() to check if a user is
     * logged in or not may not be sufficent as the method may return a guest's identity.
     *
     * @return boolean
     */
    public function logged_in()
    {
        $session = Session::instance();
        return ($session->get(self::_gen_session_key(self::_LOGGED_IN), false) === true);
    }

    /**
     *
     *
     * @return Identity
     */
    public function current_identity()
    {
        if (!isset($this->identity)) {
            $this->identity = $this->get_identity($this->logged_in() ? Identity_User::ROOT : Identity_User::GUEST);
        }
        return $this->identity;
    }

    /**
     *
     *
     * @return Identity
     */
    public function get_identity($id)
    {

        if (strtolower($this->config['admin_user']) == strtolower($id)) {
            $id = Identity_User::ROOT;
        } else if (strtolower($id) == "guest") {
            $id = Identity_User::GUEST;
        }

        $identity = new Seth_Identity_User($id, (($id == Identity_User::ROOT) ? $this->config['admin_user'] : "Guest"));
        if ($id == Identity_User::ROOT) {
            $identity->add_group($this->get_group(Identity_Group::ADMIN));
            $identity->add_group($this->get_group(Identity_Group::REGISTERED));
        } else {
            $identity = new Seth_Identity_User(Identity_User::GUEST, 'Guest');
            $identity->add_group($this->get_group(Identity_Group::UNREGISTERED));
        }
        $identity->add_group($this->get_group(Identity_Group::ALL));

        return $identity;
    }

    /**
     *
     *
     * @return Identity_Group
     */
    public function get_group($id)
    {
        if (strtolower($id) == "creator" || $id == Identity_Group::CREATOR) {
            return new Seth_Identity_Group(Identity_Group::CREATOR, 'Creator');
        } else if (strtolower($id) == "author") {
            return new Seth_Identity_Group(Identity_Group::AUTHOR, 'Author');
        } else if (strtolower($id) == "author") {
            return new Seth_Identity_Group(Identity_Group::OWNER, 'Owner');
        } else if (strtolower($id) == "admin") {
            return new Seth_Identity_Group(Identity_Group::ADMIN, 'Admin');
        } else if (strtolower($id) == "unregistered") {
            return new Seth_Identity_Group(Identity_Group::UNREGISTERED, 'Unregistered');
        } else if (strtolower($id) == "registered") {
            return new Seth_Identity_Group(Identity_Group::REGISTERED, 'Registered');
        } else if (strtolower($id) == "all") {
            return new Seth_Identity_Group(Identity_Group::ALL, 'All');
        }
        return null;
    }
}