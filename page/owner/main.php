<?php

class page_xCivil_page_owner_main extends page_componentBase_page_owner_main {
	function init(){
		parent::init();

		$this->app->layout->add('H1',null,'page_title')->setHTML('<i class="fa fa-shopping-cart"></i> '.$this->component_name. '<small> Used for various civil enginerring related work');
		// $this->app->layout->template->trySetHTML('page_title');
			
		$xcivil_m = $this->app->top_menu->addMenu($this->component_name);
		$xcivil_m->addItem(array('Dashboard','icon'=>'gauge-1'),'xCivil_page_owner_dashboard');
		$xcivil_m->addItem(array('Google Survey','icon'=>'gauge-1'),'xCivil_page_owner_google_survey');

	}


	function page_config(){
		$this->add('H1')->set('Default Config Page');
	}
}