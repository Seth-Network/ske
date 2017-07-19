<?php

interface Content_Menu_Link extends Content_Menu_Item {
	
	const ATT_TITLE = 'title';
	const ATT_HREF = 'href';
	const ATT_TARGET = 'target';
	
	/**
	 * Returns the menu link's title or null, if no title is available
	 * 
	 * @return String
	 */
	public function get_title();
	
	/**
	 * Returns the menu link's target URL used in the anchor tag's href-attribute.
	 * 
	 * @return String
	 */
	public function get_href();
	
	/**
	 * Returns the menu link's target value used in the anchor tag's target-attribute.
	 * 
	 * @return String
	 */
	public function get_target();
}