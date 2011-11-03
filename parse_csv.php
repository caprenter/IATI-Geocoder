<?php
/*
 *=== International Aid Transparency Initiative (IATI) Geocoder Information ===
 * 
 *    International Aid Transparency Initiative (IATI) Geocoder is an application to generate IATI compliant
 *    location XML from a specific set of data supplied by the World bank.
 *    This may be useful for other datasets and other transformations.
 *
 *    This file is part of International Aid Transparency Initiative (IATI) Geocoder.
 *    Copyright (C) 2011 David Carpenter
 *    Made and paid for by Development Initiatives (http://www.devinit.org/)
 *
 *    International Aid Transparency Initiative (IATI) Geocoder is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    International Aid Transparency Initiative (IATI) Geocoder is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with International Aid Transparency Initiative (IATI) Geocoder.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *Contact Information
 *caprenter@gmail.com
 *
*/
?>

<?php
$geodata = array();
$missing_codes = array();
//if csv file contains headers and you want to skip them call the script with
//php parse_csv.php headers=TRUE
$headers = $_SERVER['argv'][1];

//Parse the csv file and get the whole thing into a great big array
if (($handle = fopen("allworld.csv", "r")) !== FALSE) {
    //Ignore headers if set:
    if($headers == TRUE) {
      fgets($handle); // read and ignore the first line
    } 
    while (($data = fgetcsv($handle, 0, ',','"')) !== FALSE) { //set string length parameter to 0 lets us pull in very long lines.
        //if ($data[2] != NULL && $data[3] != NULL) { //no point if we don't have a lat and lng
          $geodata[] = array( "id" => $data[1], 
                              "lat" => $data[2], 
                              "lng" => $data[3],
                              "accuracy" => $data[4],
                              "adm2" => $data[8],
                              "adm2_code" =>$data[11],
                              "adm1" => $data[14],
                              "adm1_code" => $data[17],
                              "country" => $data[20],
                              "geoname_id" => $data[7],
                              "geoname" => $data[25]
                              );
        //}
    }
    fclose($handle);
}

//print_r($codes);
//die;

//Get the IATI Country code list into a usable array of "code"=>"name" format
$country_data = file_get_contents("Country.json"); 
$country_data = json_decode($country_data, true);
//print_r($country_data['codelist']);
//die;
foreach ($country_data['codelist']['Country'] as $country) {
  $countries[$country['code']] = $country['name'];
}
//print_r($countries);
//die;



// create a new XML document
$doc = new DomDocument('1.0','UTF-8');
$root = $doc->createElement('iati-activities');
$root = $doc->appendChild($root);

foreach ($geodata as $geo) {
  $activity = $doc->createElement('iati-activity');
  $activity = $root->appendChild($activity);
  
  $id = $doc->createElement('iati-identifier');
  $id = $activity->appendChild($id);
  
  $value = $doc->createTextNode('44000-' . $geo['id']);
  $value = $id->appendChild($value);
  
  $location = $doc->createElement('location');
  $location = $activity->appendChild($location);
  
  $administrative = $doc->createElement('administrative');
  $administrative = $location->appendChild($administrative);
  
  //Country attribute...
  //Find country code
  if ($geo['country'] !=NULL) {
    //Find corresponding key to Country string in $countries array
    switch ($geo['country']) {
       case 'Bosnia and Herzegovina':
        $geo['country'] = 'Bosnia-Herzegovina';
        break;
      case 'Central African Republic':
        $geo['country'] = 'Central African Rep.';
        break;
      case 'Congo, Democratic Republic of':
      case 'Congo, Republic of':
        $geo['country'] = 'Congo, Dem. Rep.';
        break;
      case 'Gambia, The':
        $geo['country'] = 'Gambia';
        break;
      //case 'Kosovo':
       // $geo['country'] = '';
      //  break;
      case 'Lao People\'s Democratic Republic':
        $geo['country'] = 'Laos';
        break;
      //case 'Nepal':
        //$geo['country'] = 'Gambia';
        //break;
      case 'Sao Tome and Principe':
        $geo['country'] = 'Sao Tome & Principe';
        break;
      case 'St. Vincent and the Grenadines':
        $geo['country'] = 'St.Vincent & Grenadines';
        break;
      case 'Vietnam':
        $geo['country'] = 'Viet Nam';
        break;
      case 'Yemen, Republic of':
        $geo['country'] = 'Yemen';
        break;
      case 'Zambia ':
        $geo['country'] = 'Zambia';
        break;
      }
      
      
    //Special cases for countries not on the code list
    if ($geo['country'] == 'Nepal') {
      $code = 'NP';
    } elseif ($geo['country'] == 'Kosovo') {
      $code = 'XK';
    } else {
      $code = array_search($geo['country'], $countries);
    }
    //echo $key;
    if ($code != FALSE) { //array_search above returns False if not found
      $administrative->setAttribute('country', $code);
    } else {
      array_push($missing_codes,$geo['country']);
    }
  }
  
  //adm codes
  if ($geo['adm2_code'] !=NULL) {
    //$administrative->setAttribute('adm2', $geo['adm2_code']);
    $administrative->setAttribute('adm2', $geo['adm2_code']);
  }
  if ($geo['adm1_code'] !=NULL) {
    //$administrative->setAttribute('adm1', $geo['adm1_code']);
    $administrative->setAttribute('adm1', $geo['adm1_code']);
  }

  
  $administrative_text = $geo['adm2'] . ',' . $geo['adm1']. ',' . $geo['country'];
  $administrative_text = trim($administrative_text,",");
  $value = $doc->createTextNode($administrative_text);
  $value = $administrative->appendChild($value);
  
  //reset - not needed??
  $geo['country'] = NULL;
  
  //Coordinates element...
  if ($geo['lat'] != NULL && $geo['lng'] != NULL) {
    $co_ords = $doc->createElement('coordinates');
    $co_ords = $location->appendChild($co_ords);
    $co_ords->setAttribute('latitude', $geo['lat']);
    $co_ords->setAttribute('longitude', $geo['lng']);
    if ($geo['accuracy'] !=NULL) {
      $co_ords->setAttribute('precision', $geo['accuracy']);
    }
  }
  
  //gazetter entry - all taken from the GEOCODES column
  if ($geo['geoname_id'] !=NULL) {
    $gazetteer = $doc->createElement('gazetteer-entry');
    $gazetteer = $location->appendChild($gazetteer);
    $gazetteer->setAttribute('gazetteer-ref', 'GEO');
      
    $value = $doc->createTextNode($geo['geoname_id']);
    $value = $gazetteer->appendChild($value);
  }
  //Use Geoname for NAME element
  if ($geo['geoname'] !=NULL) {
    $name = $doc->createElement('name');
    $name = $location->appendChild($name);

    $value = $doc->createTextNode($geo['geoname']);
    $value = $name->appendChild($value);
  }
  
  //What's misisng
  //description
  //location-type
  //
}

$doc->formatOutput = true;
$doc->save("test.xml");
$xml_string = $doc->saveXML();
echo $xml_string;

//print_r(array_unique($missing_codes));
//print_r($missing_codes);
?>
