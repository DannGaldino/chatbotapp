<?php
    enviar_mensagem();
    $data = json_decode(file_get_contents('php://input'), true);
    echo $data;
    if( ($data['messages'][0]['fromMe'] === false) && ($data['messages'][0]['type'] == "conversation") ) {
        echo "Mensagem recebida";
        //enviar_mensagem();
    }
    function enviar_mensagem(){
        $url = 'https://apistart01.megaapi.com.br/rest/sendMessage/megastart-MASNy5ZFidP/text';
        $headers = array(
            //'Accept: */*',
            'Authorization: Bearer MASNy5ZFidP',
            'Content-Type: application/json'
        );
        $data = array(
            'messageData' => array(
                'to' => '5511966053698@s.whatsapp.net',
                'text' => 'SE CHEEGOU, SÓ SUCESSO!'
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