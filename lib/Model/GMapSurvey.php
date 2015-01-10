<?php

namespace xCivil;


class Model_GMapSurvey extends \Model_Table {
	public $table = "xcivil_gmap_surveys";

	function init(){
		parent::init();

		$this->hasOne('xCivil/Project','project_id');
		$this->addField('name');

		$this->add('filestore/Field_File','lat_lng_file_id')->mandatory(true);
		$this->addField('chainage');
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->hasMany('xCivil/GMapSurveyData','gmap_survey_id');


		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function get_kml(){
		$dom = new \DOMDocument('1.0','UTF-8');

		//Create the root KML element and append it to the Document
		$node = $dom->createElementNS('http://earth.google.com/kml/2.1','kml');
		$parNode = $dom->appendChild($node);

		//Create a Folder element and append it to the KML element
		$fnode = $dom->createElement('Folder');
		$folderNode = $parNode->appendChild($fnode);

		//Iterate through the MySQL results
		$row="";
		$importer = new \CSVImporter(getcwd().'/../'.$this['lat_lng_file'],true,',');
		$data = $importer->get();

		foreach ($data as $dt) {
			$row.= $this->commaToDecimal($dt['Longitude']).','.$this->commaToDecimal($dt['Latitude']).',0 ';
		}

		//Create a Placemark and append it to the document
		$node = $dom->createElement('Placemark');
		$placeNode = $folderNode->appendChild($node);

		//Create an id attribute and assign it the value of id column
		$placeNode->setAttribute('id','linestring1');

		//Create name, description, and address elements and assign them the values of 
		//the name, type, and address columns from the results

		$nameNode = $dom->createElement('name','My path');
		$placeNode->appendChild($nameNode);
		$descNode= $dom->createElement('description', 'This is the path that I took through my favorite restaurants in Seattle');
		$placeNode->appendChild($descNode);

		//Create a LineString element
		$lineNode = $dom->createElement('LineString');
		$placeNode->appendChild($lineNode);
		$exnode = $dom->createElement('extrude', '1');
		$lineNode->appendChild($exnode);
		$almodenode =$dom->createElement('altitudeMode','relativeToGround');
		$lineNode->appendChild($almodenode);

		//Create a coordinates element and give it the value of the lng and lat columns from the results

		$coorNode = $dom->createElement('coordinates',$row);
		$lineNode->appendChild($coorNode);
		$kmlOutput = $dom->saveXML();

		//assign the KML headers. 
		
		return str_replace("\n", "", str_replace("\\", "", $kmlOutput));
	}

	function do_survey(){

		if($this->ref('xCivil/GMapSurveyData')->count()->getOne()> 0)
			return "ALREADY HAVE DATA, PLEASE REMOVE EXISTING DATA FIRST";

		$importer = new \CSVImporter(getcwd().'/../'.$this['lat_lng_file'],true,',');
		$data = $importer->get();
		$str ="";
		// 1 = 1389.8064439857
		// 2 = 370.62461693952
		// 3 = 860.40874371982
		for ($i=0; $i < count($data)-1; $i++) { 
			$distance = $this->calcDistance($data[$i],$data[$i+1]);
			$samples = ceil($distance/$this['chainage']);
			// check if going greater than 512 (google limit)
			if($samples > 500){
				echo $i. ' Going out of 500 range ';
			}

			$json_string = $this->getEvelvations($data[$i],$data[$i+1],$samples);
			// echo $json_string .' --- ';
			$elevations = json_decode($json_string,true);
			$elevations = $elevations['results'];

			foreach ($elevations as $elv) {
				$this->ref('xCivil/GMapSurveyData')->addGoogleData($elv['location']['lat'],$elv['location']['lng'],$elv['elevation'],$allow_same_point = false);
			}

		}
		return $this;
	}

	function getEvelvations($point_1,$point_2, $samples){
		$point_1_lat= $this->commaToDecimal($point_1['Latitude']);
		$point_1_lng= $this->commaToDecimal($point_1['Longitude']);

		$point_2_lat= $this->commaToDecimal($point_2['Latitude']);
		$point_2_lng= $this->commaToDecimal($point_2['Longitude']);

		$url = "https://maps.googleapis.com/maps/api/elevation/json?path=$point_1_lat,$point_1_lng|$point_2_lat,$point_2_lng&samples=$samples";
		// return $url;
		return file_get_contents($url);
	}

	function calcDistance($point_1, $point_2){
		return $this->calcDistanceLatLong(
				$this->commaToDecimal($point_1['Longitude']),
				$this->commaToDecimal($point_1['Latitude']),
				$this->commaToDecimal($point_2['Longitude']),
				$this->commaToDecimal($point_2['Latitude'])
			);
	}

	function commaToDecimal($cordinate){
		$cordinate_exploded = explode(",", $cordinate);
		return $this->DMStoDEC($cordinate_exploded[0],$cordinate_exploded[1],$cordinate_exploded[2]);
	}

	function DMStoDEC($deg,$min,$sec)
	{

	    return $deg+((($min*60)+($sec))/3600);
	}    

	function DECtoDMS($dec)
	{

	// Converts decimal longitude / latitude to DMS
	// ( Degrees / minutes / seconds ) 

	    $vars = explode(".",$dec);
	    $deg = $vars[0];
	    $tempma = "0.".$vars[1];

	    $tempma = $tempma * 3600;
	    $min = floor($tempma / 60);
	    $sec = $tempma - ($min*60);

	    return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	}

	function calcDistanceLatLong ($Lon1, $Lat1, $Lon2, $Lat2, $units = "meter"){
        $Difference = 3958.75 * acos(  sin($Lat1/57.2958) * sin($Lat2/57.2958) + cos($Lat1/57.2958) * cos($Lat2/57.2958) * cos($Lon2/57.2958 - $Lon1/57.2958));
        
        switch ($units){
            default:
            case "":
            case "miles":
                $Difference = $Difference * 1;
                break;
            case "yards":
                $Difference = $Difference * 1760;
                break;
            case "parsec":
                $Difference = $Difference * 0.0000000000000521553443;
                break;
            case "nauticalmiles":
                $Difference = $Difference * 0.868974087;
                break;
            case "nanometer":
                $Difference = $Difference * 1609344000000;
                break;
            case "millimeter":
                $Difference = $Difference * 1609344;
                break;
            case "mil":
                $Difference = $Difference * 63360000;
                break;
            case "micrometer":
                $Difference = $Difference * 1609344000;
                break;
            case "meter":
                $Difference = $Difference * 1609.344;
                break;
            case "lightyear":
                $Difference = $Difference * 0.0000000000001701114356;
                break;
            case "kilometer":
                $Difference = $Difference * 1.609344;
                break;
            case "inches":
                $Difference = $Difference * 63360;
                break;
            case "hectometer":
                $Difference = $Difference * 16.09344;
                break;
            case "furlong":
                $Difference = $Difference * 8;
                break;
            case "feet":
                $Difference = $Difference * 5280;
                break;
            case "dekameter":
                $Difference = $Difference * 160.9344;
                break;
            case "centimeter":
                $Difference = $Difference * 160934.4;
                break;
        }
        return $Difference;
    } 

}