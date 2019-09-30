<?php
//  Get the original data
$rawMETAR = "KMIA 301853Z 04013G19KT 10SM BKN035 BKN060 BKN250 31/21 A2994 RMK AO2 SLP138 T03110211";
echo "The given METAR is: " . $rawMETAR . '<br>';


//  Break up the METAR string
$brokenMETAR = explode(" ", $rawMETAR);
echo print_r($brokenMETAR) . "<br><br>";


//  Check to make sure that the METAR is starting legit
$legitMETAR = true;

if(strlen($brokenMETAR[0]) === 4) {
    if(preg_match('~[0-9]+~', $brokenMETAR[0])) {
        $legitMETAR = false;
    }
    else {
        $fieldName = $brokenMETAR[0];
    }
}
else {
    $legitMETAR = false;
}


//  Start the reading process

//  Get default values
$isAutomated = 'No';
$weatherConditionsArray = array();
$cloudLayerArray = array();
$remarkPoint = 0;

if($legitMETAR) {
    
    for($i = 1; $i < count($brokenMETAR); $i++) {

        //  Find the wind values
        if($brokenMETAR[$i] == 'AUTO') {
            $isAutomated = 'Yes';
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
            if(strlen($brokenMETAR[$i]) >= 2 && strlen($brokenMETAR[$i]) <= 6){
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
    echo "Something is wrong with your METAR!";
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



//  Display the data in a nice format
echo "<br>" . '<h3>Field Information</h3>';
echo 'Field ICAO Identifier: ' . $fieldName  . '<br>';
echo 'Fully-automated METAR: ' . $isAutomated . '<br>';

echo "<br>" . '<h3>Weather Information</h3>';
echo 'Time of report: Day ' . $reportingTimeDay . ' of the month @ ' . substr_replace($reportingTimeHours, ':', 2, 0) . ' Zulu (UTC)<br>';
echo 'Wind Heading: ' . $windHeading . '&deg;'. compassRose($windHeading) . '<br>';
echo 'Wind Speed: ' . ltrim($windSpeed, '0') . ' knots (' . knots2MPH($windSpeed) . ' mph)<br>';
if(isset($gustSpeed)) {
    echo 'Gust Speed: ' . ltrim($gustSpeed, '0') . ' knots (' . knots2MPH($gustSpeed) . ' mph)<br>';
}
echo 'Visibility: ' . $visibilityValue . ' statute miles.<br>';
echo 'Temperature: ' . $temperature . '&deg;C (' . celsius2Fahrenheit($temperature) . '&deg;F)<br>';
echo 'Dew Point: ' . $dewpoint . '&deg;C (' . celsius2Fahrenheit($dewpoint) . '&deg;F)<br>';
echo 'Altimeter Setting: ' . $altimeterSetting . ' InHg (' . inHg2HPa($altimeterSetting) . ' HPa)<br>';
echo "<br>" . '<h3>Weather Conditions</h3>';
foreach($weatherConditionsArray as $condition) {
    echo $condition . '<br>';
}
echo "<br>" . '<h3>Cloud Layers</h3>';
foreach($cloudLayerArray as $cloud) {
    echo $cloud . '<br>';
}


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
        return "N";
    }
    if($arg > 11.25 && $arg < 33.75) {
        return "NNE";
    }
    if($arg > 33.75 && $arg < 56.25) {
        return "NE";
    }
    if($arg > 56.25 && $arg < 78.75) {
        return "ENE";
    }
    if($arg > 78.75 && $arg < 101.25) {
        return "E";
    }
    if($arg > 101.25 && $arg < 123.75) {
        return "ESE";
    }
    if($arg > 123.75 && $arg < 146.25) {
        return "SE";
    }
    if($arg > 146.25 && $arg < 168.75) {
        return "SSE";
    }
    if($arg > 168.75 && $arg < 191.25) {
        return "S";
    }
    if($arg > 191.25 && $arg < 213.75) {
        return "SSW";
    }
    if($arg > 213.75 && $arg < 236.25) {
        return "SW";
    }
    if($arg > 236.25 && $arg < 258.75) {
        return "WSW";
    }
    if($arg > 258.75 && $arg < 281.25) {
        return "W";
    }
    if($arg > 281.25 && $arg < 303.75) {
        return "WNW";
    }
    if($arg > 303.75 && $arg < 326.25) {
        return "NW";
    }
    if($arg > 326.25 && $arg < 348.75) {
        return "NNW";
    }
}
function buildCloudString($arg, $array) {
    $cloudType = substr($arg, 0, 3);

    if($cloudType == 'SKC') {
        $cloudStr = "No clouds/Sky clear";
    }
    if($cloudType == 'NCD') {
        $cloudStr = "Nil Cloud detected";
    }
    if($cloudType == 'CLR') {
        $cloudStr = "No clouds below 12,000 ft";
    }
    if($cloudType == 'NSC') {
        $cloudStr = "No (nil) significant cloud";
    }
    if($cloudType == 'FEW') {
        $cloudStr = "Few clouds";
    }
    if($cloudType == 'SCT') {
        $cloudStr = "Scattered clouds";
    }
    if($cloudType == 'BKN') {
        $cloudStr = "Broken clouds";
    }
    if($cloudType == 'OVC') {
        $cloudStr = "Overcast clouds";
    }
    if($cloudType == 'VV') {
        $cloudStr = "Clouds cannot be seen because of fog or heavy precipitation, so vertical visibility is given instead.";
    }

    if(strlen($arg) == 6) {
        $cloudAlt = substr($arg, 3 , 6) . '00';
        $cloudAlt = ltrim($cloudAlt, '0');

        $cloudStr = $cloudStr . ' at ' . $cloudAlt . 'ft';
    }

    //  Insert values into an array
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
        } 
        if($arg === 'RA') {
            $condition = 'Rain';
        } 
        if($arg === 'SN') {
            $condition = 'Snow';
        } 
        if($arg === 'SG') {
            $condition = 'Snow Grains';
        } 
        if($arg === 'IC') {
            $condition = 'Ice Crystals';
        } 
        if($arg === 'PL') {
            $condition = 'Ice Pellets';
        } 
        if($arg === 'GR') {
            $condition = 'Hail';
        } 
        if($arg === 'GS') {
            $condition = 'Small Hail or Snow Pellets';
        } 
        if($arg === 'UP') {
            $condition = 'Unknown precipitation';
        } 
        if($arg === 'BR') {
            $condition = 'Mist';
        } 
        if($arg === 'FG') {
            $condition = 'Fog';
        } 
        if($arg === 'FU') {
            $condition = 'Smoke';
        } 
        if($arg === 'DU') {
            $condition = 'Dust';
        } 
        if($arg === 'SA') {
            $condition = 'Sand';
        } 
        if($arg === 'HZ') {
            $condition = 'Haze';
        } 
        if($arg === 'PY') {
            $condition = 'Spray';
        } 
        if($arg === 'VA') {
            $condition = 'Volcanic Ash';
        } 
        if($arg === 'PO') {
            $condition = 'Well-Developed Dust/Sand Whirls';
        } 
        if($arg === 'SQ') {
            $condition = 'Squalls';
        } 
        if($arg === 'FC') {
            $condition = 'Funnel Cloud';
        } 
        if($arg === 'SS') {
            $condition = 'Sandstorm';
        } 
        if($arg === 'DS') {
            $condition = 'Duststorm';
        } 
        if($arg === 'TS') {
            $condition = 'Thunderstorm';
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
    if(count($array) == 0) {
        $array[0] = $weatherStr;
    }
    else {
        $array[count($array)] = $weatherStr;
    }

    return $array;
}
?>