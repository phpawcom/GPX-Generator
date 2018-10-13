<?php
require_once('class.PolylineEncoder.php');
class gpxGen extends PolylineEncoder {
    private $apiKey;
    public $logInputs = false;
    public $logDir = 'logs';
    public $polyline = '';
    public $connectTimeOut = 10;
    public function __construct($apiKey, $logInputs = false){
        if(empty($apiKey)) exit('You must write Google API Key');
        $this->apiKey = $apiKey;
        $this->logInputs = $logInputs;
        if($this->logInputs && !is_dir($this->logDir)){
            if(is_writable('./')){
                mkdir($this->logDir, 0777);
                $this->protect_directory_access($this->logDir, 'comprehensive');
            }else{
                trigger_error("Unable to create Logs directory");
            }            
        }
    }
    public function protect_directory_access($directory, $type = 'access'){
		$file = $directory.'/.htaccess';
		if(!file_exists($file)){
            $handle = fopen($file, 'w');
            fwrite($handle, ($type == 'comprehensive'? 'deny from all' : 'Options -Indexes'));
            fclose($handle);
        }
	}
    private function api($url, $header=false, $sslvar=false){
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        if(!$header)
          curl_setopt($connection, CURLOPT_HEADER, 0);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $this->connectTimeOut);
		if(!$sslvar)
		  curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($connection);
        curl_close($connection);
        return $content;
	}
    private function _is_double($input){
        return is_double($input) || is_numeric($input)? true : false;
    }
    public function get_route(){
		$output = array();
        if ( func_num_args() == 4){
            $args = array_slice(func_get_args(), 0);
            if(count(array_filter($args, array($this, '_is_double'))) === count($args)){
                $data = $this->api('https://maps.googleapis.com/maps/api/directions/json?origin='.$args[0].','.$args[1].'&destination='.$args[2].','.$args[3].'&key='.$this->apiKey);
                if(!empty($data)){
                    $data = json_decode($data, true);
                    if(is_array($data) && count($data) > 0){
                        $output['distance'] = $data['routes'][0]['legs'][0]['distance'];
                        $output['duration'] = $data['routes'][0]['legs'][0]['duration'];
                        $output['polyline'] = $data['routes'][0]['overview_polyline']['points'];
                        $this->polyline = $output['polyline'];
                        if($this->logInputs){
                            $this->doLog(time()."\t".date('Y-m-d H:i', time())."\t".$args[0].','.$args[1]."\t".$args[2].','.$args[3]."\t".$_SERVER['REMOTE_ADDR']."\t".$_SERVER['HTTP_USER_AGENT']);
                        }
                    }
                }
            }else{
                trigger_error("get_route(): all inputs should be double");
            }
        }else{
            trigger_error("get_route(): you must enter latitude and longitude in this format: from_latitude, from_longitude, to_latitude, to_longitude");
        }
		return is_array($output) && count($output) > 0? $output : array();
	}
    public function route2array(){
        $output = array();
        if(!empty($this->polyline)){
           $output = PolylineEncoder::decodeValue($this->polyline);
        }
        return $output;
    }
    public function create_gpx(){
        $xmlData = '';
        if(count($this->route2array()) > 0){
            $time = new DateTime();
            $dom  = new DomDocument("1.0", "UTF-8");
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            $gpx  = $dom->createElement('gpx');
            $gpx->setAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
            $gpx->setAttribute('creator', 'MapSource 6.16.1');
            $gpx->setAttribute('creator', 'gpx generator');
            $gpx->setAttribute('version', '1.0');
            $gpx->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $gpx->setAttribute('xsi:schemaLocation', 'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd');
            $trk = $dom->createElement('trk');
            $trk->appendChild($dom->createElement('name', 'emulate'));
            $trkseg = $dom->createElement('trkseg');
            foreach($this->route2array() as $i => $dp){
                $trkpt =  $dom->createElement('trkpt');
                $trkpt->setAttribute('lat', $dp['x']);
                $trkpt->setAttribute('lon', $dp['y']);
                $trkpt->appendChild($dom->createElement('ele', '0.000000'));
                $trkpt->appendChild($dom->createElement('time', str_replace(' ', 'T', $time->modify('+1 second')->format('Y-m-d H:i:s')).'Z'));
                $trkseg->appendChild( $trkpt );
            }
            $trk->appendChild($trkseg);
            $gpx->appendChild( $trk );
            $dom->appendChild( $gpx );
            $xmlData  = $dom->saveXML();
        }else{
            trigger_error("There is no route created");
        }
        return $xmlData;
    }
    private function doLog($input){
        if(is_writable($this->logDir)){
            $preappend = ''; 
            $file = $this->logDir.'/'.date('Y-m', time()).'.log';
            if(!file_exists($file)){
                $preappend = "Timestamp\tDate\tStarting LatLng\tEnding LatLng\tIP\tBrowser";
            }
            $handle = fopen($file, 'a');
            fwrite($handle, (!empty($preappend)? $preappend."\n" : '').$input."\n");
            fclose($handle);
        }else{
            trigger_error("Cannot write in " + $this->logDir);
        }
    }
}
?>