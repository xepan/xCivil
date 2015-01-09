<?php

namespace xCivil;

class Model_Client extends \Model_Table {
	public $table= 'xcivil_clients';

	function init(){
		parent::init();

		$this->addField('name');
		$this->hasMany('xCivli/Project','client_id');

		$this->add('dynamic_model/Controller_AutoCreator');

	}
}