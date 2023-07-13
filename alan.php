<?php
$data1 = file_get_contents("php://input");
$event = json_decode($data1, true);
$phonenumber = $event['key']['remoteJid'];
$dataType = $event['messageType'];
//if(isset($event)){
//if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false) {
if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false && $event['message'][$dataType] === 'PEDIDO') {
    //Here, you now have event and can process them how you like e.g Add to the database or generate a response
    $file = 'log.txt';  
    $data1 =json_encode($event)."\n";  
    file_put_contents($file, $data1, FILE_APPEND | LOCK_EX);
    $url = 'https://api5.megaapi.com.br/rest/sendMessage/megaapi-MZAyU7l7QPMtYZE90fDNATF0b1/text';
    $headers = array(
        'Authorization: Bearer MZAyU7l7QPMtYZE90fDNATF0b1',
        'Content-Type: application/json'
    );
    $data = array(
        'messageData' => array(
            'to' => $phonenumber,
            'text' => $dataType
        )
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);
    echo $curl;

    // Processar a resposta
    if ($response === false) {
        // Erro na solicitação
        echo 'Erro na solicitação.';
        echo $response;
    } else {
        // Sucesso na solicitação
        echo 'Solicitação enviada com sucesso.';
        echo 'Resposta: ' . $response;
    }
}
?>