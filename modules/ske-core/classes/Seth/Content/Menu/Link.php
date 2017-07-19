<?php
class SCMS_Content_Menu_Link implements Content_Menu_Link {
	
	/**
	 * Sets and gets this menu link's title @chainable
	 *
	 * @param String $value        
	 * @return String|SCMS_Content_Menu_Link
	 */
	public function title($value = NULL) {
		if ($value === null) {
			return $this->attribute(self::ATT_TITLE);
		}
		$this->attribute(self::ATT_TITLE, $value);
		return $this;
	}
	
	/**
	 * Sets and gets this menu link's href @chainable
	 *
	 * @param String $value        
	 * @return String|SCMS_Content_Menu_Link
	 */
	public function href($value = NULL) {
		if ($value === null) {
			return $this->attribute(self::ATT_HREF);
		}
		$this->attribute(self::ATT_HREF, $value);
		return $this;
	}
	
	/**
	 * Sets and gets this menu link's target @chainable
	 *
	 * @param String $value        
	 * @return String|SCMS_Content_Menu_Link
	 */
	public function target($value = NULL) {
		if ($value === null) {
			return $this->attribute(self::ATT_TARGET);
		}
		$this->attribute(self::ATT_TARGET, $value);
		return $this;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Link::get_title()
	 */
	public function get_title() {
		return $this->title();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Link::get_href()
	 */
	public function get_href() {
		return $this->href();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Link::get_target()
	 */
	public function get_target() {
		return $this->target();
	}
}