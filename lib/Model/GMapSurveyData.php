<?php

namespace xCivil;

class Model_GMapSurveyData extends \Model_Table {
	public $table ="xcivil_gmap_survey_data";

	function init(){
		parent::init();

		$this->hasOne('xCivil/GMapSurvey','gmap_survey_id');

		$this->addField('longitude');
		$this->addField('latitude');
		$this->addField('utm_x');
		$this->addField('utm_y');
		$this->addField('elevation');

		$this->addField('is_from_google')->type('boolean')->defaultValue(false);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function addGoogleData($latitude,$longitude,$elevation, $allow_same_point){
		if($this->loaded()) $this->unload();

		if(!$allow_same_point){
			$check_existing = $this->add('xCivil/Model_GMapSurveyData');
			$check_existing->addCondition('longitude',$longitude);
			$check_existing->addCondition('latitude',$latitude);
			$check_existing->addCondition('elevation',$elevation);
			$check_existing->tryLoadAny();
			if($check_existing->loaded()) return;
		}

		$gp = $this->add('xCivil/gPoint');
		$gp->setLongLat($longitude,$latitude);
		$gp->convertLLtoTM(null);

		$this['longitude'] = $longitude;
		$this['latitude'] = $latitude;
		$this['elevation'] = $elevation;
		$this['utm_x'] = $gp->E();
		$this['utm_y'] = $gp->N();
		$this['is_from_google'] = true;
		$this->save();
	}

}