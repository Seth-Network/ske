<?php
class Controller_Admin_Api_Localization extends Controller {
	// ************************************************************************
	// A T T S
	// ************************************************************************
	
	// ************************************************************************
	// M E T H O D S
	// ************************************************************************
	public function action_missing() {
		$lang = $this->request->param('lang');
			
		if ($lang == null) {
			throw new Kohana_HTTP_Exception_400("Missing parameter 'lang'!");
		}
		
		if ($this->request->method() == 'GET') {
			
			$rows = array();
			foreach (Seth_I18n::get_missing() as $key => $missing_locales) {
				$rows[$key] = Seth_I18n::get_for_lang($key, $lang);
			}
			$this->view->json_content = json_encode($rows);
		} else if ($this->request->method() == 'POST') {
			$rows = array();
			$key = $this->request->post('key');
			$value = $this->request->post('value');
			
			Seth_I18n::write($lang, $key, $value);
			$rows['progress'] = "done";
			$this->view->json_content = json_encode($rows);
		}
		else {
			throw new Kohana_HTTP_Exception_400("Bad request method!");
		}
	}
	

}