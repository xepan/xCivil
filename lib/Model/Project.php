<?php

namespace xCivil;

class Model_Project extends \Model_Table{
	public $table ="xcivil_projects";

	function init(){
		parent::init();
		
		$this->hasOne('xCivil/Client','client_id')->mandatory(true);
		$this->addField('name');

		$this->add('dynamic_model/Controller_AutoCreator');

	}

}