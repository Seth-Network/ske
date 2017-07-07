<?php

interface Content_Menu_Item {
	
	const ATT_LABEL = 'label';
	const ATT_ICON = 'icon';
	const ATT_IS_ACTIVE = 'active';
	const ATT_TYPE = 'type';
	
	/**
	 * Value for attribute Content_Menu_Item::ATT_TYPE that this menu item
	 * is a separator.
	 * 
	 * @var String
	 */
	const SEPARATOR = 'separator';
	
	/**
	 * Value for attribute Content_Menu_Item::ATT_TYPE that this menu item
	 * is a menu section's title
	 *
	 * @var String
	 */
	const SECTION_TITLE = 'title';
	
	/**
	 * Returns the menu item's label or null, if the item does not have any label at all
	 * 
	 * @return String
	 */
	public function get_label();
	
	/**
	 * Returns TRUE if this menu item is currently active/selected
	 * 
	 * @return boolean
	 */
	public function is_active();
	
	/**
	 * Returns the attribute value for given key. If the attribute is not defined, the default value is returned.
	 * Specific attributes can be provided by each implementation and it depends on the defining
	 * as on the using implementation if the attributes are used.
	 * 
	 * The content menu's API does provided some predefined attribute names (see constants starting with ATT_), some which are also
	 * available as direct methods. Additional attributes can be added.
	 * 
	 * @param String $key
	 * @param mixed $defaut
	 * @return mixed
	 */
	public function get_attribute($key, $defaut = NULL);
}