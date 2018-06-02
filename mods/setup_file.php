<?php
// Write to log.
debug_log('setup_file()');


//MAINTAINER_ID

if ($update['message']['from']['id']!=MAINTAINER_ID) {
    $msg = 'I don\'t know what to do with that';
//    send_message($update['message']['chat']['id'], $msg, []);
    exit();
}

if ($update['message']['document']['mime_type']!='application/json') {
    send_message($update['message']['chat']['id'], 'Only JSON is supported', []);
    exit();
}

$f = get_file($update['message']['document']['file_id']);

$url = 'https://api.telegram.org/file/bot'.API_KEY.'/'.$f['result']['file_path'];
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// Use Proxyserver for curl if configured
if (CURL_USEPROXY == true) {
    curl_setopt($curl, CURLOPT_PROXY, CURL_PROXYSERVER);
}
debug_log($url, 'F>');
$json_response = curl_exec($curl);




//exit();


//$j = file_get_contents('gyms+stops_1527941260913.json');
$d = json_decode($json_response, true);

$pokestops = 0;
$duplicates = 0;
foreach ($d['pokestops'] as $k=>$v) {
    $query = 'SELECT COUNT(*) AS c FROM pokestops WHERE pokestop_name="'.$db->real_escape_string($v['name']).'" AND lat='.$v['lat'].' AND lon='.$v['lng'].'';
    $rs = my_query($query);
    $row = $rs->fetch_assoc();
    if ($row['c']) {
        $duplicates++;
        /* Duplicate */
    } else {
        $query = 'INSERT INTO pokestops SET `pokestop_name`="'.$db->real_escape_string($v['name']).'", lat='.$v['lat'].', lon='.$v['lng'].'';
        $rs = my_query($query);
        if ($rs) $pokestops++;
   }
}

send_message($update['message']['chat']['id'], $pokestops.' pokestops imported, '.$duplicates.' duplicates ignored.', []);



exit();
