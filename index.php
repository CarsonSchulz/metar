<!DOCTYPE html>
<html>
<?php require_once("includes/head.php"); ?>
<body>
<?php require_once("includes/nav.php"); ?>
<div class="w-100 form-intro d-flex flex-wrap align-items-center">
    <div class="container">
        <form method="post" action="results.php">
            <label for="metarInput">Enter your METAR here <span class="text-danger">(North American format ONLY)</span>:</label>
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" id="metarInput" name="metarInput">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="white" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg></button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="w-100 about-intro d-flex flex-wrap align-items-center py-2">
    <div class="container">
        <div class="card shadow">
            <div class="card-body">
                <h1 class="heading-underline">METAR Information</h1>
                <p>A METAR is a format for reporting weather information. This method is most often used by pilots for use in flight planning but is also used by meteorologists to assist in weather forecasting. A raw METAR is the most common format. This tool will take a raw METAR and convert it to a readable format.</p>
                <p>Some example METARs:</p>
                <ul>
                    <li><code>KTTN 051853Z 04011KT 1/2SM VCTS SN FZFG BKN003 OVC010 M02/M02 A3006 RMK AO2 TSB40 SLP176 P0002 T10171017</code></li>
                    <li><code>KTPA 091619Z 02009KT 10SM SCT015 BKN021 OVC280 28/24 A2999 RMK AO2 T02830239</code></li>
                    <li><code>PANC 181553Z 14017G28KT 10SM SCT055 OVC075 09/03 A3002 RMK AO2 PK WND 14028/1552 RAB14E32 SLP167 P0000 T00890028</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>