<?php
    $data1 = file_get_contents("php://input");
    $event = json_decode($data1, true);
    $phoneNumber = substr($event['key']['remoteJid'], 0, 13);
    $dataType = $event['messageType'];
    $mensagem_recebida = $event['message'][$dataType];
    $messageTimeStamp = $event['messageTimestamp'];
    date_default_timezone_set('America/Sao_Paulo');
    $data = substr(date('d-m-Y H:i:s', $messageTimeStamp), 0, 10);
    $hora = substr(date('d-m-Y H:i:s', $messageTimeStamp), 11);

    if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false){
        if (verifica_cliente($phoneNumber) === false){
            $texto = "Ola, seja bem vindo ao atendimento da Inforlaser\n\nPor favor, nos informe se você já é um cliente da Inforlaser\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
            enviar_mensagem($phoneNumber, $texto);
            $andamento = 1;
            iniciar_status($phoneNumber, $andamento);
        }

        if(verificar_status($phoneNumber) == 1){
            if($mensagem_recebida == "1"){
                $texto = "Nos informe o seu código de cliente por favor ";
                enviar_mensagem($phoneNumber, $texto);
                $mensagem_recebida = "";
                $andamento = 2;
                atualizar_status($phoneNumber, $andamento);
            }
        }
        if(verificar_status($phoneNumber) == 2){
            if ($mensagem_recebida != ""){
                $codigo = $mensagem_recebida;
                if(consultar_razaoSocial($codigo) != ""){
                    $texto = "Você representa a empresa " . consultar_razaoSocial($mensagem_recebida) . "?\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                    enviar_mensagem($phoneNumber, $texto);
                    $andamento = 3;
                    atualizar_status($phoneNumber, $andamento);
                }else{
                    $texto = "Cliente não encontrado!!!\n\nPor favor, verifique se o codigo informado está correto e tente novamente!";
                    enviar_mensagem($phoneNumber, $texto);
                }
            }
        }
        if(verificar_status($phoneNumber) == 3){
            if($mensagem_recebida == "1"){
                $texto = "Qual serviço você deseja solicitar?\n\nEscolha uma das opções abaixo:\n*1*. Chamado\n*2*. Suprimento";
                enviar_mensagem($phoneNumber, $texto);
                $andamento = 4;
                atualizar_status($phoneNumber, $andamento);
                $mensagem_recebida = "";
            }
            if($mensagem_recebida == "2"){
                $texto = "Nos informe o seu código de cliente por favor";
                enviar_mensagem($phoneNumber, $texto);
                $mensagem_recebida = "";
                $andamento = 2;
                atualizar_status($phoneNumber, $andamento);
            }
        }
        if(verificar_status($phoneNumber) == 4){
            if($mensagem_recebida == "1"){
                $texto = "Nos informe o ativo da máquina para qual você deseja abrir o chamado:";
                enviar_mensagem($phoneNumber, $texto);
                $mensagem_recebida = "";
                $andamento = 5;
                atualizar_status($phoneNumber, $andamento);
            }
            if($mensagem_recebida == "2"){
                $texto = "Nos informe o ativo da máquina para qual você deseja solicitar um suprimento:";
                enviar_mensagem($phoneNumber, $texto);
                $andamento = 7;
                atualizar_status($phoneNumber, $andamento);
            }
        }
        if(verificar_status($phoneNumber) == 5){
            if($mensagem_recebida != ""){
                $ativo = $mensagem_recebida;
                $texto = "A máquina em questão é: " . consultar_equipamento($ativo) . "?\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                enviar_mensagem($phoneNumber, $texto);
                $mensagem_recebida = "";
                $andamento = 6;
                atualizar_status($phoneNumber, $andamento);
            }
        }

        if(verificar_status($phoneNumber) == 6){
            if($mensagem_recebida == "1"){
                $texto = "Chamado para ativo " . $ativo . " aberto!\nNossa equipe entrará em contato com você";
                enviar_mensagem($phoneNumber, $texto);
                registrar_protocolo($codigo, $phoneNumber, $data, $hora, $comentario, $ativo);
                $texto = "*Atendimento finalizado!!!*";
                enviar_mensagem($phoneNumber, $texto);
                $mensagem_recebida = "";
                $andamento = 0;
                atualizar_status($phoneNumber, $andamento);
                excluir_status();
            }
        }
    }

    //if(isset($event)){
    //if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false) {
    /*if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false) {
        //Here, you now have event and can process them how you like e.g Add to the database or generate a response
        $file = 'log.txt';  
        $data1 =json_encode($event)."\n";
        file_put_contents($file, $data1, FILE_APPEND | LOCK_EX);
        
        if ($mensagem_recebida != 'Sim' && $mensagem_recebida != 'Não'){
            //$texto = "Telefone: " . $phoneNumber . "\nMensagem: " . $mensagem_recebida . "\nData: " . $data . "\nHora: " . $hora;
            $texto = "Ola, seja bem vindo ao atendimento da Inforlaser\n\nPor favor, nos informe se você já é um cliente da Inforlaser enviando *Sim* ou *Não*";
            enviar_mensagem($phoneNumber, $texto);
        }else{
            if($event['message'][$dataType] === 'Sim'){
                //abrir_protocolo($phoneNumber, 'locacao');
                $texto = "Nos informe o seu código de cliente por favor";
                enviar_mensagem($phoneNumber, $texto);
            }
            if($event['message'][$dataType] === 'Não'){
                //abrir_protocolo($phoneNumber, 'evento');
                $texto = "Protocolo para eventos iniciado. Nossa equipe entrará em contato com você!!!";
                enviar_mensagem($phoneNumber, $texto);
                $type = "evento";
                abrir_protocolo($phoneNumber, $type);
            }
        }
    }*/
    function enviar_mensagem($numeroTelefone, $text){
        $url = 'https://api5.megaapi.com.br/rest/sendMessage/megaapi-MZAyU7l7QPMtYZE90fDNATF0b1/text';
        $headers = array(
            'Authorization: Bearer MZAyU7l7QPMtYZE90fDNATF0b1',
            'Content-Type: application/json'
        );
        $dados = array(
            'messageData' => array(
                'to' => $numeroTelefone,
                'text' => $text
            )
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dados));
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

    function abrir_protocolo($numeroTelefone, $tipo){
        $conexao = mysqli_connect('192.168.176.129', 'root', 'je131199', 'chatbot');

        // Verifique se a conexão foi estabelecida com sucesso
        if (!$conexao) {
            die('Erro ao conectar ao MySQL: ' . mysqli_connect_error());
        }

        $sql = "INSERT INTO protocolo_chat_teste (telefone, tipo) VALUES ('$numeroTelefone', '$tipo')";
        if (mysqli_query($conexao, $sql)) {
            echo "Dados inseridos com sucesso!";
        } else {
            echo "Erro ao inserir dados: " . mysqli_error($conexao);
        }
        mysqli_close($conexao);
    }

    function verifica_cliente($numeroTelefone){
        // Configurações do banco de dados
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "SELECT telefone FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function iniciar_status($numeroTelefone, $andamento){
        // Configurações do banco de dados
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "INSERT INTO status_conversa (telefone, andamento) values ($numeroTelefone, $andamento)";
        $result = $conn->query($sql);

        //Fechando a conexão
        $conn->close();

    };

    function atualizar_status($numeroTelefone, $andamento){
        // Configurações do banco de dados
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "UPDATE status_conversa SET andamento = $andamento WHERE telefone = $numeroTelefone";
        $conn->query($sql);

        //Fechando a conexão
        $conn->close();
    };

    function excluir_status(){
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "DELETE FROM status_conversa WHERE andamento = 0";
        $conn->query($sql);

        //Fechando a conexão
        $conn->close();
    }

    function registrar_protocolo($value1, $value2, $value3, $value4, $value5, $value6){
        // Configurações do banco de dados
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "INSERT INTO chamados (codigo, telefone, data, hora, comentario, ativo) VALUES ($value1, $value2, $value3, $value4, $value5, $value6)";
        $result = $conn->query($sql);

        //Fechando a conexão
        $conn->close();
    }

    function verificar_status($numeroTelefone){
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "chatbot";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "SELECT andamento FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $retorno = $row['andamento'];
        }

        //Fechando a conexão
        $conn->close();

        return $retorno;
    }

    function consultar_razaoSocial($codigoCliente){
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "SELECT RazaoSocia FROM Clientes WHERE Cod_Cliente = $codigoCliente";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $retorno = $row['RazaoSocia'];
        }

        //Fechando a conexão
        $conn->close();

        return $retorno;
    }

    function consultar_equipamento($ativo){
        $servername = "192.168.176.129";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        // Consulta para verificar se o ID existe
        $sql = "SELECT equipamento FROM equip_contrato WHERE ativo = $ativo";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $retorno = $row['equipamento'];
        }

        //Fechando a conexão
        $conn->close();

        return $retorno;
    }
?>