<?php
    


    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apistart01.megaapi.com.br/rest/instance/megastart-MASNy5ZFidP',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer MASNy5ZFidP'
      ),
    ));

    $data = json_decode(file_get_contents('php://input'), true);
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    
    echo var_dump($data);
    if( ($data['messages'][0]['fromMe'] === false) && ($data['messages'][0]['type'] == "chat") ) {
        
        echo "teste!";
        
        $nome = $data['messages'][0]['senderName'];
		$phone = explode ("@", $data['messages'][0]['chatId'])[0];
		$body = $data['messages'][0]['body'];

        enviar_mensagem("5511966053698", "Estamos testando");
        
    }
    function enviar_mensagem($numero, $text){
        echo $numero;
        echo $text;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apistart01.megaapi.com.br/rest/sendMessage/megastart-MASNy5ZFidP/text',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "messageData": {
                "to": "' . $numero . '",
                "text": "' . $text . '"
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer MASNy5ZFidP'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
    
?>