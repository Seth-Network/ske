<?php

class Seth_Config_File_Writer extends Kohana_Config_File_Reader implements Kohana_Config_Writer {


	/**
	 * Writes the passed config for $group
	 *
	 * Returns chainable instance on success or throws
	 * Kohana_Config_Exception on failure
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config) {

		$configuration = $this->load($group);

		$configuration = Arr::merge($configuration, array($key=>$config));

		// workaround for unsetting a configuration key
		if ( $config === NULL ) {
			unset($configuration[$key]);
		}

		$data  = Kohana::FILE_SECURITY ."\n\n";
		$data .= "/**\n";
		$data .= " * This is a PHP generated configuration file by ". __CLASS__ ."\n";
		$data .= " * For detailed documentation for this configuration see the \n";
		$data .= " * module's configuration file.\n";
		$data .= " *\n";
		$data .= " * @author: ". __CLASS__ ."\n";
		$data .= " * @date: ". date("Y-m-d H:i:s") ."\n";
		$data .= " */\n\n";
		$data .= "return ". var_export($configuration, true) .";";

		if ( file_put_contents(APPPATH . $this->_directory .'/'. $group .'.php' , $data) === false) {
			throw new Kohana_Exception("Can not save configuration file :file: Method failed!", array(":file" => APPPATH . $this->_directory .'/'. $group .'.php'));
		} else {
			return $this;
		}

	}
}