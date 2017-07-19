<?php
class Controller_Admin extends Controller {
	
	// ************************************************************************
	// A T T S
	// ************************************************************************
	/**
	 * @var  String  Template file used by the view
	 */
	public $template = '';
	
	/**
	 * @var  boolean  auto render template
	 **/
	public $auto_render = TRUE;
	
	/**
	 *
	 * @var View
	 */
	public $view = null;
	
	/**
	 * @Inject
	 * @var DI_Container
	 */
	protected $di;
	
	// ************************************************************************
	//	M E T H O D S
	// ************************************************************************
	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before() {
		parent::before();
		Multiframe::set_environment('html', SCMS_Multiframe_Environment::factory()->template_folder('gentallela-admin//fragments'), true);
		$this->view = Smarty_View::factory(NULL);
		
		// DO SOME CHECKS HERE IF ADMIN IS AVAILABLE: ACCESS RESTRICTION ETC
		# TODO
	}
	
	/**
	 * Assigns the view [View] as the request response if it is set.
	 */
	public function after() {
	
		if ( $this->auto_render ) {
			$this->view->request = $this->request;
			$this->view->ctrl = $this;
			$this->view->set_filename($this->template);
			$this->response->body($this->view->render());
		}
		parent::after();
	}
	
	public function action_list() {
		
	}
	
}