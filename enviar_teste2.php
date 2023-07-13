<?php
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
                "to": "5511966053698@s.whatsapp.net",
                "text": "Olá, estamos testando"
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
?>