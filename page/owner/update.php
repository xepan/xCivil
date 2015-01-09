<?php

class page_xCivil_page_owner_update extends page_componentBase_page_update {
		
	public $git_path="https://github.com/xepan/xCivil.git"; // Put your components git path here

	function init(){
		parent::init();

		// 
		// Code To run before update
		
		$this->update($dynamic_model_update=true); // All modls will be dynamic executed in here
		
		// Code to run after update
	}
}