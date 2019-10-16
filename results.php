<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["metarInput"])) {
        echo "Missing Input";
        header("Location: index.php");
        die();
    } else {
        $rawMETAR = sanitize_input($_POST["metarInput"]);

        require_once("parsedata.php");
    }
} else {
    header("Location: index.php");
    die();
}

function sanitize_input($METAR) {
    $METAR = stripslashes($METAR);
    $METAR = htmlspecialchars($METAR);
    $METAR = htmlentities($METAR);
    return $METAR;
}
?>
<!DOCTYPE html>
<html>
<?php require_once("includes/head.php"); ?>
<body>
<?php require_once("includes/nav.php"); ?>
<div class="results-header d-flex flex-wrap align-items-center">
    <div class="container">
        <p class="small">Displaying the given METAR:</p>
        <p><?php echo htmlentities($rawMETAR); ?></p>
    </div>
</div>
<div class="results-info-cont d-flex flex-wrap align-items-center py-3">
    <div class="container">
        <div class="card px-2 py-4 shadow">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="w-100 h-100 border-right pr-2">
                            <p class="small mb-0">METAR for:</p>
                            <h1><?php echo $fieldName ?></h1>
                            <p class="small mb-0 mt-4">Report Time:</p>
                            <p><?php echo 'Day ' . $reportingTimeDay . ' of the month @ ' . substr_replace($reportingTimeHours, ':', 2, 0) . ' Zulu (UTC)' ?></p>
                            <p class="small mb-0 mt-4">Airport Name:</p>
                            <p><?php echo $airportName; ?></p>
                            <p class="small mb-0 mt-4">City:</p>
                            <p><?php echo $airportCity; ?></p>
                            <p class="small mb-0 mt-4">State:</p>
                            <p><?php echo $airportState; ?></p>
                            <p class="small mb-0 mt-4">Latitude:</p>
                            <p><?php echo $airportLat; ?></p>
                            <p class="small mb-0 mt-4">Longitude:</p>
                            <p><?php echo $airportLon; ?></p>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="container-fluid">
                            <div class="row text-center border-bottom">
                                <div class="col-md-3">
                                    <i class="wi wi-thermometer results-icon"></i>
                                    <h2><?php echo $temperature . '&deg;C' ?></h2>
                                    <p class="small">Temperature</p>
                                </div>
                                <div class="col-md-3">
                                    <i class="wi wi-strong-wind results-icon"></i>
                                    <h2><?php if($windSpeed == '00') { echo 0 . ' knots'; } else { echo ltrim($windSpeed, '0') . ' knots'; } ?></h2>
                                    <p class="small">Wind Speed</p>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                        $dirFull = explode("|", compassRose($windHeading));
                                        $windHeadingStr = $dirFull[0];
                                        $compassIcon = $dirFull[1];
                                    ?>
                                    <i class="wi wi-wind <?php echo $compassIcon ?> results-icon"></i>
                                    <h2>
                                        <?php echo $windHeading . '&deg;'. $windHeadingStr ?>
                                    </h2>
                                    <p class="small">Wind Direction</p>
                                </div>
                                <div class="col-md-3">
                                    <i class="wi wi-barometer results-icon"></i>
                                    <h2><?php echo $altimeterSetting . ' inHg' ?></h2>
                                    <p class="small">Pressure</p>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4 pl-0">
                                    <h5>Current Conditions</h5>
                                    <ul class="list-group list-group-flush">
                                        <?php if(empty($weatherConditionsArray)): ?>
                                        <li class="list-group-item">No cloud data to display.</li>
                                        <?php else: ?>
                                        <?php 
                                        foreach($weatherConditionsArray as $condition) {
                                            $conditionFull = explode("|", $condition);

                                            echo '<li class="list-group-item"><i class="wi ' . $conditionFull[1] . ' results-icon-add mr-2"></i>' . $conditionFull[0] . '</li>';
                                        }
                                        ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Cloud Layers</h5>
                                    <ul class="list-group list-group-flush">
                                        <?php if(empty($cloudLayerArray)): ?>
                                        <li class="list-group-item">No cloud data to display.</li>
                                        <?php else: ?>
                                        <?php 
                                        foreach($cloudLayerArray as $cloud) {
                                            $cloudFull = explode("|", $cloud);

                                            echo '<li class="list-group-item"><i class="wi ' . $cloudFull[1] . ' results-icon-add mr-2"></i>' . $cloudFull[0] . '</li>';
                                        }
                                        ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Additional Remarks</h5>
                                    <ul class="list-group list-group-flush">
                                        <?php if(isset($gustSpeed)): ?>
                                        <li class="list-group-item text-danger">Wind Gusts: <?php echo $gustSpeed . ' knots.'?></li>
                                        <?php endif; ?>
                                        <li class="list-group-item">Visibility: <?php echo $visibilityValue . ' statute miles.'?></li>
                                        <li class="list-group-item">Dew Point: <?php echo $dewpoint . '&deg;C' ?></li>
                                        <?php if(isset($isAutomated)): ?>
                                        <li class="list-group-item">Station is completely automated.</li>
                                        <?php else: ?>
                                        <li class="list-group-item">Station is not completely automated.</li>
                                        <?php endif;?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>