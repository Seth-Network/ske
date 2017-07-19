<?php

class SCMS_Content_Menu implements Content_Menu {
	
	/**
	 * @var Array(Content_Menu_Item)
	 */
	protected $items = array();
	
	/**
	 * Sets and gets this menu's items
	 * 
	 * @chainable
	 * @param Array(Content_Menu_Item) $value
	 * @return Array(Content_Menu_Item)|SCMS_Content_Menu
	 */
	public function items(array $value = NULL) {
		if ( $value === null ) {
			return $this->items;
		}
		$this->items = $value;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see Content_Menu::get_items()
	 */
	public function get_items() {
		return $this->items();
	}
}