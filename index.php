<?php
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
include('config.php'); ## Make sure to change config.sample.php to config.php after adding API Key
include('class.core.php'); 
$gpxGen = new gpxGen($apiKey);

//$route = $gpxGen->get_route(23.57777, 58.41254, 23.54255, 58.25882); ## Generate route between two points using Google Maps API
//print_r($route);
//print_r($gpxGen->route2array());
if(!empty($route['polyline'])){
    header('Content-type: text/xml');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="'.date('Y-m-d H_m_s', time()).'.gpx"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo $gpxGen->create_gpx();
}

?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>GPX Generator for Android Studio Emulator</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <h2>GPX Generator <span class="text-muted">for Android Studio Emulator</span></h2>
        <div class="panel panel-default">
            <div class="panel-body">This is a basic tool to generate .gpx file to help you in Android emulator to test your navigation app between two points</div>
        </div>
    </div>
    <style type="text/css"></style>
    <script type="text/javascript">
        $(document).ready(function(){
            "use strict";
//            alert('test');
        })
    </script>
</body>
</html>

