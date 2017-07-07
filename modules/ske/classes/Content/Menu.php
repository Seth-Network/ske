<?php

interface Content_Menu extends Content_Menu_Link {

	/**
	 * Return the array of items available in this menu
	 * 
	 * @return Array(Content_Menu_Item)
	 */
	public function get_items();
}