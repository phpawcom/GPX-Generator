<?php
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
include('config.php'); ## Make sure to change config.sample.php to config.php after adding API Key
include('class.core.php');
$gpxGen = new gpxGen($apiKey);
if($_POST){
    $from = explode(',', $_POST['from_gps']);
    $to = explode(',', $_POST['to_gps']);
    $route = $gpxGen->get_route($from[0], $from[1], $to[0], $to[1]); ## Generate route between two points using Google Maps API
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
}else{
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>GPX Generator for Android Studio Emulator</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&amp;language=en&amp;libraries=places" type="text/javascript"></script>
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
        <form method="post" action="index.php?op=download" name="download_gpx">
            <div class="panel panel-default">
                <div class="panel-heading">Create Path</div>
                <div class="panel-body">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <input type="text" name="from" id="from" class="form-control google-places-gps-from" placeholder="From" />
                            <input type="hidden" name="from_gps" id="from_gps" class="form-control" />
                        </div>
                        <div class="form-group">
                            <div id="map_from" class="map"></div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <input type="text" name="to" id="to" class="form-control google-places-gps-to" placeholder="To" />
                            <input type="hidden" name="to_gps" id="to_gps" class="form-control" />
                        </div>
                        <div class="form-group">
                            <div id="map_to" class="map"></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group" style="text-align: center"><button type="submit" class="btn btn-default">Download</button></div>
                </div>
            </div>
        </form>
        <div class="panel panel-default">
            <div class="panel-heading">Used Classes / Thanks to:</div>
            <div class="panel-body">
                <ul>
                    <li>dyaaj: <a href="https://github.com/dyaaj/polyline-encoder" target="_blank">Polyline Encoder</a></li>
                    <li>Dimitri: <a href="https://stackoverflow.com/a/22319706" target="_blank">GPX Format</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div style="text-align: center">
        <span class="text-muted">Get this tool source code @ <a href="https://github.com/phpawcom/gpx-generator" target="_blank">Github</a></span>
    </div>
    <style type="text/css">
        .map {
            background-color: #858585;
            height: 300px;
        }
    </style>
    <script type="text/javascript">
        var map, mapOptions, mgeocoder;
        var __load_google_places = function(){
            "use strict";
            if($('.google-places-gps-from').length && typeof google === 'object'){
                var input = document.getElementById($('.google-places-gps-from').attr('id'));
                var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    document.getElementById('from_gps').value = place.geometry.location.lat()+','+place.geometry.location.lng();
                    if($('input[name=from_gps]').length){
                        $('input[name=from_gps]').trigger('change');
                    }
                });
            }
            if($('.google-places-gps-to').length && typeof google === 'object'){
                var input = document.getElementById($('.google-places-gps-to').attr('id'));
                var autocomplete2 = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete2, 'place_changed', function () {
                    var place2 = autocomplete2.getPlace();
                    document.getElementById('to_gps').value = place2.geometry.location.lat()+','+place2.geometry.location.lng();
                    if($('input[name=to_gps]').length){
                        $('input[name=to_gps]').trigger('change');
                    }
                });
            }
        };
        var __load_google_map = function(holderID, Lat, Lng, zoomLevel){
            "use strict";
            if(typeof google === 'object' && typeof google.maps === 'object'){
                mgeocoder = new google.maps.Geocoder();
                var marker;
                zoomLevel = zoomLevel.length === 0? 13 : parseInt(zoomLevel);
                mapOptions = {
                  zoom: zoomLevel,
                  center: new google.maps.LatLng(Lat, Lng),
                  mapTypeId: google.maps.MapTypeId.HYBRID
                };
                map = new google.maps.Map(document.getElementById(holderID), mapOptions);
                marker = new google.maps.Marker({
                  map: map,
                  draggable: true,
                  animation: google.maps.Animation.DROP,
                  position: new google.maps.LatLng(Lat, Lng)
                });
                google.maps.event.addListener(marker, 'drag', function(){
//                    __toggleBounce(marker.getPosition());
                    if(holderID == 'map_to'){
                        $('input[name=to_gps]').val(marker.getPosition().lat() + ',' + marker.getPosition().lng());
                    }else{
                        $('input[name=from_gps]').val(marker.getPosition().lat() + ',' + marker.getPosition().lng());
                    }
                });
                google.maps.event.addListener(marker, 'dragend', function(){
//                    __google_map_geocodeLatLng(marker.getPosition());
                });
            }
        };
        $(document).ready(function(){
            "use strict";
            __load_google_places();
//            alert('test');
        });
        $(document).on('change', 'input[name=from_gps]', function(){
            "use strict";
            var latlng = $(this).val().split(',');
            __load_google_map('map_from', latlng[0], latlng[1], 12);
        });
        $(document).on('change', 'input[name=to_gps]', function(){
            "use strict";
            var latlng = $(this).val().split(',');
            __load_google_map('map_to', latlng[0], latlng[1], 12);
        });
    </script>
</body>
</html>
<?php 
     }
?>


