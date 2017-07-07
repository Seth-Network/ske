<?php defined('SYSPATH') OR die('No direct script access.');

class Seth_Asset_View extends Kohana_View {
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see Kohana_View::set_filename()
	 */
	public function set_filename($file)
	{
		if ( file_exists($file) ) {
			// Store the file path locally
			$this->_file = $file;
			return $this;
		}
		return parent::set_filename($file);
	}
}