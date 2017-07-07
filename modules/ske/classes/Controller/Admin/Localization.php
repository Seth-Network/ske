<?php
class Controller_Admin_Localization extends Controller_Admin {
	// ************************************************************************
	// A T T S
	// ************************************************************************
	
	// ************************************************************************
	// M E T H O D S
	// ************************************************************************
	public function action_list() {
		$provided_language = (($provided_language = $this->request->query("a")) == null) ? I18n::$lang:$provided_language;
		$requested_language = (($requested_language = $this->request->query("b")) == null) ? I18n::$lang:$requested_language;
		
		$this->view->lang_a = $provided_language;
		$this->view->lang_b = $requested_language;
		$this->view->title_frame = __('Missing localization');
		$this->template = 'gentallela-admin/l18n/list.tpl';
	}
}