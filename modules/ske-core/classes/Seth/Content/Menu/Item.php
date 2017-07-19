<?php
class SCMS_Content_Menu_Item implements Content_Menu_Item {
	
	/**
	 *
	 * @var Array(String=>mixed)
	 */
	protected $attributes = array();
	
	/**
	 * Sets and gets this menu item's label
	 * @chainable
	 * 
	 * @param String $value        
	 * @return String|SCMS_Content_Menu_Item
	 */
	public function label($value = NULL) {
		if ($value === null) {
			return $this->attribute(self::ATT_LABEL);
		}
		$this->attribute(self::ATT_LABEL, $value);
		return $this;
	}
	
	/**
	 * Sets and gets if this menu item is active
	 * @chainable
	 * 
	 * @param boolean $value        
	 * @return boolean|SCMS_Content_Menu_Item
	 */
	public function active($value = NULL) {
		if ($value === null) {
			return $this->attribute(self::ATT_IS_ACTIVE);
		}
		$this->attribute(self::ATT_IS_ACTIVE, $value);
		return $this;
	}
	
	/**
	 * Sets and gets this menu item's attribute
	 * @chainable
	 * 
	 * @param Array(String=>mixed) $value        
	 * @return Array(String=>mixed)|SCMS_Content_Menu_Item
	 */
	public function attributes(array $value = NULL) {
		if ($value === null) {
			return $this->attributes;
		}
		$this->attributes = $value;
		return $this;
	}
	
	/**
	 * Sets and gets this menu item's attribute
	 * @chainable
	 * 
	 * @param Array(String=>mixed) $value        
	 * @return Array(String=>mixed)|SCMS_Content_Menu_Item
	 */
	public function attribute($key, $value = NULL) {
		if ($value === null && func_num_args() == 1) {
			return (isset($this->attributes[$key])) ? $this->attributes[$key]:null;
		}
		$this->attributes[$key] = $value;
		return $this;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Item::get_label()
	 */
	public function get_label() {
		return $this->label();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Item::is_active()
	 */
	public function is_active() {
		return (bool) $this->active();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Content_Menu_Item::get_attribute()
	 */
	public function get_attribute($key, $defaut = NULL) {
		if (isset($this->attributes[$key])) {
			return $this->attributes[$key];
		}
		return $defaut;
	}
}