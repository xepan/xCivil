<?php

class page_xCivil_page_owner_google_survey extends page_xCivil_page_owner_main {

	function page_index(){

		$this->app->layout->add('H1',null,'page_title')->setHTML('Google Map Survey');

		$gmap_survey_model = $this->add('xCivil/Model_GMapSurvey');

		$crud = $this->app->layout->add('CRUD');

		$crud->setModel($gmap_survey_model);
		$crud->addAction('do_survey',array('toolbar'=>false));
		$crud->addAction('get_kml',array('toolbar'=>false));

		if(!$crud->isEditing()){
			$crud->grid->add_sno();
			$crud->grid->addColumn('expander','data');
		}
	}

	function page_data(){
		$this->api->stickyGET('xcivil_gmap_surveys_id');
		$gmap_survey = $this->add('xCivil/Model_GMapSurvey')->load($_GET['xcivil_gmap_surveys_id']);

		$crud = $this->add('CRUD');
		$crud->setModel($gmap_survey->ref('xCivil/GMapSurveyData')->setOrder('id'));
		if(!$crud->isEditing()){
			$crud->grid->add('misc/Export');
			$crud->grid->add_sno();
			$crud->grid->addPaginator(100);
		}

	}
}		