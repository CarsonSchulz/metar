<?php
//  Break up the METAR string
$brokenMETAR = explode(" ", $rawMETAR);


//  Check to make sure that the METAR is starting legit
$legitMETAR = true;

if(strlen($brokenMETAR[0]) === 4) {
    if(preg_match('~[0-9]+~', $brokenMETAR[0])) {
        $legitMETAR = false;
    }
    else {
        $fieldName = $brokenMETAR[0];
    }

    $apiURL = "https://api.aeronautical.info/dev/?airport=" . $fieldName . "&include=demographic&include=geographic";
    $file_headers = @get_headers($apiURL);
    if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.0 404 Not Found') {
        $legitMETAR = false;
    }
    else {
        $airportData = file_get_contents($apiURL);
        $airportObj = json_decode($airportData);

        $airportName = $airportObj->name;
        $airportCity = $airportObj->city;
        $airportState = $airportObj->state_name;
        $airportLat = $airportObj->latitude_dms;
        $airportLon = $airportObj->longitude_dms;

        if($airportState == null) {
            $airportState = $airportObj->county;
        }

    }

}
else {
    $legitMETAR = false;
}


//  Start the reading process

//  Get default values
$isAutomated = false;
$weatherConditionsArray = array();
$cloudLayerArray = array();
$remarkPoint = 0;

if($legitMETAR) {
    
    for($i = 1; $i < count($brokenMETAR); $i++) {

        //  Find the wind values
        if($brokenMETAR[$i] == 'AUTO') {
            $isAutomated = true;
        }


        //  Get the time of the report
        if(strpos($brokenMETAR[$i], 'Z') && strlen($brokenMETAR[$i]) === 7) {
            $reportingTime = $brokenMETAR[$i];
        }


        //  Get the wind speed if there is no gust
        if(strpos($brokenMETAR[$i], 'KT') && strlen($brokenMETAR[$i]) === 7) {
            $windHeading = substr($brokenMETAR[$i], 0, -4);
            $windSpeed = substr($brokenMETAR[$i], 3, -2);
        }

        
        //  Get the wind speed with the gusts
        if(strpos($brokenMETAR[$i], 'KT') && strpos($brokenMETAR[$i], 'G')) {
            $windHeading = substr($brokenMETAR[$i], 0, -7);
            $windSpeed = substr($brokenMETAR[$i], 3, -5);
            $gustSpeed = substr($brokenMETAR[$i], 6, -2);
        }


        //  Get the visibility
        if(strpos($brokenMETAR[$i], 'SM')) {
            $visibilityValue = $brokenMETAR[$i];

            //  See if there is a value before
            if (strlen($brokenMETAR[$i - 1]) === 1) {
                $visibilityValue = $brokenMETAR[$i - 1] . ' ' . $visibilityValue;
            }
        }


        //  Get some weather conditions
        if(strpos($brokenMETAR[$i],'+') !== false || strpos($brokenMETAR[$i],'-') !== false || strpos($brokenMETAR[$i],'VC') !== false || strpos($brokenMETAR[$i],'MI') !== false || strpos($brokenMETAR[$i],'BC') !== false || strpos($brokenMETAR[$i],'DR') !== false || strpos($brokenMETAR[$i],'BL') !== false || strpos($brokenMETAR[$i],'SH') !== false || strpos($brokenMETAR[$i],'TS') !== false || strpos($brokenMETAR[$i],'FZ') !== false || strpos($brokenMETAR[$i],'PR') !== false || strpos($brokenMETAR[$i],'DZ') !== false || strpos($brokenMETAR[$i],'RA') !== false || strpos($brokenMETAR[$i],'SN') !== false || strpos($brokenMETAR[$i],'SG') !== false || strpos($brokenMETAR[$i],'IC') !== false || strpos($brokenMETAR[$i],'PL') !== false || strpos($brokenMETAR[$i],'GR') !== false || strpos($brokenMETAR[$i],'GS') !== false || strpos($brokenMETAR[$i],'UP') !== false || strpos($brokenMETAR[$i],'BR') !== false || strpos($brokenMETAR[$i],'FG') !== false || strpos($brokenMETAR[$i],'FU') !== false || strpos($brokenMETAR[$i],'DU') !== false || strpos($brokenMETAR[$i],'SA') !== false || strpos($brokenMETAR[$i],'HZ') !== false || strpos($brokenMETAR[$i],'PY') !== false || strpos($brokenMETAR[$i],'VA') !== false || strpos($brokenMETAR[$i],'PO') !== false || strpos($brokenMETAR[$i],'SQ') !== false || strpos($brokenMETAR[$i],'FC') !== false || strpos($brokenMETAR[$i],'+FC') !== false || strpos($brokenMETAR[$i],'SS') !== false || strpos($brokenMETAR[$i],'DS')) {
            if(strlen($brokenMETAR[$i]) >= 2 && strlen($brokenMETAR[$i]) <= 5){
                $weatherConditionsArray = buildWeatherString($brokenMETAR[$i], $weatherConditionsArray);
            }
        }

        
        //  Lets start getting the cloud layers
        if(strpos($brokenMETAR[$i], 'SKC') !== false || strpos($brokenMETAR[$i], 'NCD') !== false || strpos($brokenMETAR[$i], 'CLR') !== false || strpos($brokenMETAR[$i], 'NSC') !== false || strpos($brokenMETAR[$i], 'FEW') !== false || strpos($brokenMETAR[$i], 'SCT') !== false || strpos($brokenMETAR[$i], 'BKN') !== false || strpos($brokenMETAR[$i], 'OVC') !== false || strpos($brokenMETAR[$i], 'VV') !== false) {
            if(strlen($brokenMETAR[$i]) >= 6){
                $cloudLayerArray = buildCloudString($brokenMETAR[$i], $cloudLayerArray);
            }
        }


        //  Now we can get the temperature and humidity
        if(strpos($brokenMETAR[$i], '/') !== false && strlen($brokenMETAR[$i]) >= 5 && strlen($brokenMETAR[$i]) <= 7 && strpos($brokenMETAR[$i], 'SM') == false) {
            $tempNDew = $brokenMETAR[$i];
        }


        //  Lastly, we try and get the altimeter setting
        if($brokenMETAR[$i][0] == 'A' && strlen($brokenMETAR[$i]) === 5){
            $altimeterSetting = $brokenMETAR[$i];
        }


        //  We dont want to read the remark yet, so for now lets stop the loop there
        if($brokenMETAR[$i] === 'RMK') {
            $remarkPoint = $i;
            break;
        }

    }

}
else {
    header("Location: index.php");
    die();
}

//  Convert the data to a readable format
//  We will start with the reporting time
$reportingTimeDay = substr($reportingTime, 0, 2);
$reportingTimeHours = substr($reportingTime, 2, -1);

//  Lets also do some edits to visibility
$visibilityValue = substr($visibilityValue, 0, -2);

//  Time to format the temperature and dew point
$tempNDew = str_replace('M', '-', $tempNDew);
$tempNDewArray = explode('/', $tempNDew);
$temperature = (int)$tempNDewArray[0];
$dewpoint = (int)$tempNDewArray[1];

//  Now for the altimeter setting
$altimeterSetting = substr($altimeterSetting, 1);
$altimeterSetting = substr_replace($altimeterSetting, '.', 2, 0);


//  Needed functions
function knots2MPH($val) {
    return round($val * 1.151);
}
function inHg2HPa($val) {
    return round($val * 33.864);
}
function celsius2Fahrenheit($val) {
    return ($val * (9/5)) + 32;
}
function compassRose($arg) {
    if($arg > 348.75 || $arg < 11.25) {
        $direction = "N";
		$windIcon = "wi-towards-n";
    }
    if($arg > 11.25 && $arg < 33.75) {
        $direction = "NNE";
		$windIcon = "wi-towards-nne";
    }
    if($arg > 33.75 && $arg < 56.25) {
        $direction = "NE";
		$windIcon = "wi-towards-ne";
    }
    if($arg > 56.25 && $arg < 78.75) {
        $direction = "ENE";
		$windIcon = "wi-towards-ene";
    }
    if($arg > 78.75 && $arg < 101.25) {
        $direction = "E";
		$windIcon = "wi-towards-e";
    }
    if($arg > 101.25 && $arg < 123.75) {
        $direction = "ESE";
		$windIcon = "wi-towards-ese";
    }
    if($arg > 123.75 && $arg < 146.25) {
        $direction = "SE";
		$windIcon = "wi-towards-se";
    }
    if($arg > 146.25 && $arg < 168.75) {
        $direction = "SSE";
		$windIcon = "wi-towards-sse";
    }
    if($arg > 168.75 && $arg < 191.25) {
        $direction = "S";
		$windIcon = "wi-towards-s";
    }
    if($arg > 191.25 && $arg < 213.75) {
        $direction = "SSW";
		$windIcon = "wi-towards-ssw";
    }
    if($arg > 213.75 && $arg < 236.25) {
        $direction = "SW";
		$windIcon = "wi-towards-sw";
    }
    if($arg > 236.25 && $arg < 258.75) {
        $direction = "WSW";
		$windIcon = "wi-towards-wsw";
    }
    if($arg > 258.75 && $arg < 281.25) {
        $direction = "W";
		$windIcon = "wi-towards-w";
    }
    if($arg > 281.25 && $arg < 303.75) {
        $direction = "WNW";
		$windIcon = "wi-towards-wnw";
    }
    if($arg > 303.75 && $arg < 326.25) {
        $direction = "NW";
		$windIcon = "wi-towards-nw";
    }
    if($arg > 326.25 && $arg < 348.75) {
        $direction = "NNW";
		$windIcon = "wi-towards-nnw";
    }

    return $direction . "|" . $windIcon;
}
function buildCloudString($arg, $array) {

    $specialType = false;

    if(strpos($arg,'CB') !== false || strpos($arg,'CBMAM') !== false || strpos($arg,'CCSL') !== false || strpos($arg,'SCSL') !== false) {
        $specialType = true;
    }

    $cloudType = substr($arg, 0, 3);

    if($cloudType == 'SKC') {
        $cloudStr = "No clouds/Sky clear";
        $cloudIcon = "wi-day-sunny";
    }
    if($cloudType == 'NCD') {
        $cloudStr = "Nil Cloud detected";
        $cloudIcon = "wi-day-sunny";
    }
    if($cloudType == 'CLR') {
        $cloudStr = "No clouds below 12,000 ft";
        $cloudIcon = "wi-day-sunny";
    }
    if($cloudType == 'NSC') {
        $cloudStr = "No (nil) significant cloud";
        $cloudIcon = "wi-day-sunny";
    }
    if($cloudType == 'FEW') {
        $cloudStr = "Few clouds";
        $cloudIcon = "wi-day-sunny-overcast";
    }
    if($cloudType == 'SCT') {
        $cloudStr = "Scattered clouds";
        $cloudIcon = "wi-day-cloudy";
    }
    if($cloudType == 'BKN') {
        $cloudStr = "Broken clouds";
        $cloudIcon = "wi-cloudy";
    }
    if($cloudType == 'OVC') {
        $cloudStr = "Overcast clouds";
        $cloudIcon = "wi-cloud";
    }
    if($cloudType == 'VV') {
        $cloudStr = "Clouds cannot be seen because of fog or heavy precipitation, so vertical visibility is given instead.";
        $cloudIcon = "wi-fog";
    }

    if(strlen($arg) >= 6) {

        $cloudAlt = preg_replace('/\D/', '', $arg) . '00';
        $cloudAlt = ltrim($cloudAlt, '0');

        $cloudStr = $cloudStr . ' @ ' . $cloudAlt . 'ft';
    }

    if($specialType) {

        $arglen = strlen($arg);

        $specialTypeCode = substr($arg, 6 , $arglen);

        if($specialTypeCode == 'CB') {
            $specialTypeStr = "(Cumulonimbus clouds)";
        }
        if($specialTypeCode == 'CBMAM') {
            $specialTypeStr = "(Cumulonimbus mammatus clouds)";
        }
        if($specialTypeCode == 'CCSL') {
            $specialTypeStr = "(Cirrocumulus standing lenticular clouds)";
        }
        if($specialTypeCode == 'SCSL') {
            $specialTypeStr = "(Stratocumulus standing lenticular clouds)";
        }

        $cloudStr = $cloudStr . ' ' . $specialTypeStr;

    }

    //  Insert values into an array

    $cloudStr = $cloudStr . '|' . $cloudIcon;

    if(count($array) == 0) {
        $array[0] = $cloudStr;
    }
    else {
        $array[count($array)] = $cloudStr;
    }

    return $array;
}


function buildWeatherString($arg, $array) {

    //  Get strength modifiers
    if($arg[0] === '-') {
        $strength = "Light";
        $arg = substr($arg, 1);
    }
    else if($arg[0] === '+') {
        $strength = "Heavy";
        $arg = substr($arg, 1);
    }

    $strLen = strlen($arg);

    //  Get the descriptor
    if($strLen == 4) {
        $modifier = substr($arg, 0, -2);
        $arg = substr($arg, 2, 4);
        $strLen = strlen($arg);

        if($modifier === 'VC') {
            $vicinity = true;
        }
        if($modifier === 'MI') {
            $descriptor = 'Shallow';
        }
        if($modifier === 'BC') {
            $descriptor = 'Patches';
        }
        if($modifier === 'DR') {
            $descriptor = 'Low Drifting';
        }
        if($modifier === 'BL') {
            $descriptor = 'Blowing';
        }
        if($modifier === 'SH') {
            $descriptor = 'Showers';
        }
        if($modifier === 'TS') {
            $descriptor = 'Thunderstorm';
        }
        if($modifier === 'FZ') {
            $descriptor = 'Freezing';
        }
        if($modifier === 'PR') {
            $descriptor = 'Partial';
        }
    }

    //  Now, lets get the conditions
    if($strLen == 2) {

        if($arg === 'DZ') {
            $condition = 'Drizzle';
			$conditionIcon = "wi-showers";
        } 
        if($arg === 'RA') {
            $condition = 'Rain';
			$conditionIcon = "wi-rain";
        } 
        if($arg === 'SN') {
            $condition = 'Snow';
			$conditionIcon = "wi-snow";
        } 
        if($arg === 'SG') {
            $condition = 'Snow Grains';
			$conditionIcon = "wi-snow";
        } 
        if($arg === 'IC') {
            $condition = 'Ice Crystals';
			$conditionIcon = "wi-snowflake-cold";
        } 
        if($arg === 'PL') {
            $condition = 'Ice Pellets';
			$conditionIcon = "wi-sleet";
        } 
        if($arg === 'GR') {
            $condition = 'Hail';
			$conditionIcon = "wi-sleet";
        } 
        if($arg === 'GS') {
            $condition = 'Small Hail or Snow Pellets';
			$conditionIcon = "wi-sleet";
        } 
        if($arg === 'UP') {
            $condition = 'Unknown precipitation';
			$conditionIcon = "wi-rain-mix";
        } 
        if($arg === 'BR') {
            $condition = 'Mist';
			$conditionIcon = "wi-fog";
        } 
        if($arg === 'FG') {
            $condition = 'Fog';
			$conditionIcon = "wi-fog";
        } 
        if($arg === 'FU') {
            $condition = 'Smoke';
			$conditionIcon = "wi-smoke";
        } 
        if($arg === 'DU') {
            $condition = 'Dust';
			$conditionIcon = "wi-dust";
        } 
        if($arg === 'SA') {
            $condition = 'Sand';
			$conditionIcon = "wi-sandstorm";
        } 
        if($arg === 'HZ') {
            $condition = 'Haze';
			$conditionIcon = "wi-day-haze";
        } 
        if($arg === 'PY') {
            $condition = 'Spray';
			$conditionIcon = "wi-raindrops";
        } 
        if($arg === 'VA') {
            $condition = 'Volcanic Ash';
			$conditionIcon = "wi-volcano";
        } 
        if($arg === 'PO') {
            $condition = 'Well-Developed Dust/Sand Whirls';
			$conditionIcon = "wi-sandstorm";
        } 
        if($arg === 'SQ') {
            $condition = 'Squalls';
			$conditionIcon = "wi-rain";
        } 
        if($arg === 'FC') {
            $condition = 'Funnel Cloud';
			$conditionIcon = "wi-tornado";
        } 
        if($arg === 'SS') {
            $condition = 'Sandstorm';
			$conditionIcon = "wi-sandstorm";
        } 
        if($arg === 'DS') {
            $condition = 'Duststorm';
			$conditionIcon = "wi-sandstorm";
        } 
        if($arg === 'TS') {
            $condition = 'Thunderstorm';
			$conditionIcon = "wi-thunderstorm";
        } 
    }

    //  Lastly we have to build the string
    $weatherStr = '';

    if(isset($strength)) {
        $weatherStr = $strength . ' ';
    }
    if(isset($descriptor)) {
        $weatherStr = $weatherStr . $descriptor . ' ';
    }

    $weatherStr = $weatherStr . $condition;

    if(isset($vicinity) && $vicinity == true){
        $weatherStr = $weatherStr . ' in the vicinity';
    }

    //  Insert values into an array
    $weatherStr = $weatherStr . '|' . $conditionIcon;

    if(count($array) == 0) {
        $array[0] = $weatherStr;
    }
    else {
        $array[count($array)] = $weatherStr;
    }

    return $array;
}
?>