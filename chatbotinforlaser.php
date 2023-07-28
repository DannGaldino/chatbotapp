<?php
    $data1 = file_get_contents("php://input");
    $event = json_decode($data1, true);
    $phoneNumber = substr($event['key']['remoteJid'], 0, 13);
    $dataType = $event['messageType'];

    if ($dataType == 'extendedTextMessage'){
        $mensagem_recebida = $event['message'][$dataType]['text'];
    }else{
        $mensagem_recebida = $event['message'][$dataType];
    }

    $messageTimeStamp = $event['messageTimestamp'];
    date_default_timezone_set('America/Sao_Paulo');
    $data = substr(date('Y-m-d H:i:s', $messageTimeStamp), 0, 10);
    $hora = substr(date('d-m-Y H:i:s', $messageTimeStamp), 11);

    $file = 'log.txt';  
    $data1 =json_encode($event)."\n";  
    file_put_contents($file, $data1, FILE_APPEND | LOCK_EX);

    if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false){
        comparar_hora($phoneNumber);
        if (verifica_cliente($phoneNumber) === false){
            $texto = "Ola, seja bem vindo ao atendimento da Inforlaser\n\nPor favor, nos informe seu nome para darmos início ao atendimento";
            enviar_mensagem($phoneNumber, $texto);
            $mensagem_recebida = "";
            $andamento = 1;
            iniciar_status($phoneNumber, $andamento);
            atualizar_status("hora", $phoneNumber, $hora);
        }

        switch (verificar_status($phoneNumber)){
            case 1:
                if($mensagem_recebida != ""){
                    $nomeAbre = $mensagem_recebida;
                    atualizar_status("nome_abre", $phoneNumber, "'" . $nomeAbre . "'");
                    $texto = "Por favor, nos informe se você já é um cliente da Inforlaser\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 2;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                }
                break;
            case 2:
                if($mensagem_recebida == "1"){
                    $texto = "Nos informe o seu código de cliente por favor ";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 3;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }
                if($mensagem_recebida == "2"){
                    $texto = "Por favor, nos informe o nome da sua empresa:";
                    enviar_mensagem($phoneNumber, $texto);
                    atualizar_status("hora", $phoneNumber, $hora);
                    $mensagem_recebida = "";
                    $andamento = 21;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }
                break;
            case 21:
                if ($mensagem_recebida != ""){
                    $empresa = $mensagem_recebida;
                    atualizar_status("nome_empresa", $phoneNumber, "'" . $empresa . "'");
                    $texto = "Qual o assunto?";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 42;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }
                break;
            case 3:
                if ($mensagem_recebida != ""){
                    $codCliente = $mensagem_recebida;
                    atualizar_status("hora", $phoneNumber, $hora);
                    if(consultar_razaoSocial($codCliente) != "ERROR!"){
                        $texto = "Você representa a empresa " . consultar_razaoSocial($mensagem_recebida) . "?\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                        enviar_mensagem($phoneNumber, $texto);
                        $mensagem_recebida = "";
                        $andamento = 4;
                        atualizar_status("andamento", $phoneNumber, $andamento);
                        atualizar_status("cod_cliente", $phoneNumber, $codCliente);
                    }else{
                        $texto = "Cliente não encontrado!!!\n\nPor favor, verifique se o codigo informado está correto e tente novamente!";
                        enviar_mensagem($phoneNumber, $texto);
                        $andamento = 3;
                        atualizar_status("andamento", $phoneNumber, $andamento);
                    }
                }
                break;
            case 4:
                if($mensagem_recebida == "1"){
                    $texto = "Qual serviço você deseja solicitar?\n\nEscolha uma das opções abaixo:\n*1*. Chamado\n*2*. Suprimento\n*3*. Outros";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 5;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                    
                }
                if($mensagem_recebida == "2"){
                    $texto = "Nos informe o seu código de cliente por favor ";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 3;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                }
                break;
            case 41:
                if ($mensagem_recebida != ""){
                    $coment = $mensagem_recebida;
                    atualizar_status("comentario", $phoneNumber, $coment);
                    $nomeAbre = consulta_nome_abre_status($phoneNumber);
                    $codCliente = consulta_codcliente_status($phoneNumber);
                    $codProt = sec_users_cod_prot($codCliente); 
                    $userSig = sec_users_sigla($codCliente);
                    atualizar_status("comentario", $phoneNumber, $coment);
                    atualizar_status("data", $phoneNumber, "'" . $data . "'");
                    atualizar_status("hora", $phoneNumber, $hora);
                    $protocolo = numerar_protocolo2($phoneNumber, $nomeAbre, $codCliente, $coment, $codProt, $userSig, $data, $hora);
                    $andamento = 0;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    excluir_status();
                    $texto = "Chamado aberto.\n\nNúmero de protocolo: " . $protocolo . "\n\nAguarde o nosso contato";
                    enviar_mensagem($phoneNumber, $texto);
                    $texto = "*Esta conversa foi encerrada!*";
                    enviar_mensagem($phoneNumber, $texto);
                }
                break;
            case 42:
                if ($mensagem_recebida != ""){
                    $coment = $mensagem_recebida;
                    atualizar_status("comentario", $phoneNumber, $coment);
                    $nomeAbre = consulta_nome_abre_status($phoneNumber);
                    $codCliente = 2030;
                    $codProt = 30;
                    $userSig = "CLI";
                    atualizar_status("comentario", $phoneNumber, $coment);
                    atualizar_status("data", $phoneNumber, "'" . $data . "'");
                    atualizar_status("hora", $phoneNumber, $hora);
                    $protocolo = numerar_protocolo3($phoneNumber, $nomeAbre, $codCliente, $coment, $codProt, $userSig, $data, $hora);
                    $andamento = 0;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    excluir_status();
                    $texto = "Chamado aberto.\n\nNúmero de protocolo: " . $protocolo . "\n\nAguarde o nosso contato";
                    enviar_mensagem($phoneNumber, $texto);
                    $texto = "*Esta conversa foi encerrada!*";
                    enviar_mensagem($phoneNumber, $texto);
                }
                break;
            case 5:
                if($mensagem_recebida == "1"){
                    $cod_cliente = consulta_codcliente_status($phoneNumber);
                    $texto = "Nos informe o ativo da máquina para qual você deseja abrir o chamado: \n\n";
                    $opcao = 0;
                    $texto .= listar_equip($cod_cliente, $opcao)[0];
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 6;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                }
                if($mensagem_recebida == "2"){
                    $texto = "Nos informe o ativo do suprimento";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 62;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                }
                if($mensagem_recebida == "3"){
                    $texto = "Qual o assunto?";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 41;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }
                break;
            case 6:
                if($mensagem_recebida != ""){
                    $cod_cliente = consulta_codcliente_status($phoneNumber);
                    $opcao = $mensagem_recebida - 1;
                    $ativo = listar_equip($cod_cliente, $opcao)[1];
                    atualizar_status("ativo", $phoneNumber, "'" . $ativo . "'");
                    $texto = "Qual o problema da máquina?\n\nEscolha uma das opções abaixo:\n\n1. Enroscando papel\n2. Manchando impressão\n3. Não puxa papel no scanner\n4. Não liga\n5. Não imprime do micro\n6. Outros";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 7;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    atualizar_status("hora", $phoneNumber, $hora);
                }
                break;
            case 62:
                $ativo = $mensagem_recebida;
                atualizar_status("ativo", $phoneNumber, "'" . $ativo . "'");
                $supri = consultar_equipamento($ativo);
                atualizar_status("suprimento", $phoneNumber, "'" . $supri . "'");
                atualizar_status("hora", $phoneNumber, $hora);
                $mensagem_recebida = "";
                enviar_mensagem($phoneNumber, $supri);
                finalizar_suprimento($phoneNumber, $data, $hora);
                break;
            case 7:
                switch($mensagem_recebida){
                    case "1":
                        $problema = "Enroscando papel";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        finalizar_chamado($phoneNumber, $data, $hora);
                        break;
                    case "2":
                        $problema = "Manchando impressão";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        finalizar_chamado($phoneNumber, $data, $hora);
                        break;
                    case "3":
                        $problema = "Não puxa papel no scanner";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        finalizar_chamado($phoneNumber, $data, $hora);
                        break;
                    case "4":
                        $problema = "Não liga";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        finalizar_chamado($phoneNumber, $data, $hora);
                        break;
                    case "5":
                        $problema = "Não imprime do micro";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        finalizar_chamado($phoneNumber, $data, $hora);
                        break;
                    case "6":
                        $problema = "Outros";
                        atualizar_status("problema", $phoneNumber, $problema);
                        $mensagem_recebida = "";
                        $texto = "Descreva melhor o problema da máquina";
                        enviar_mensagem($phoneNumber, $texto);
                        $andamento = 8;
                        atualizar_status("andamento", $phoneNumber, $andamento);
                        break;
                }//Fim Switch msg recebida
            case 8:
                if($mensagem_recebida != ""){
                    $coment = $mensagem_recebida;
                    atualizar_status("comentario", $phoneNumber, $coment);
                    $mensagem_recebida = "";
                    finalizar_chamado($phoneNumber, $data, $hora);
                }
                break;
        }//Fim Switch 1
    }//Fim do primeiro if

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

    function finalizar_chamado($phoneNumber, $data, $hora){
        $coment = consulta_comentario_status($phoneNumber);
        $problema = consulta_problema_status($phoneNumber);
        $ativo = consulta_ativo_status($phoneNumber);
        $nomeAbre = consulta_nome_abre_status($phoneNumber);
        $codCliente = consulta_codcliente_status($phoneNumber);
        $codProt = sec_users_cod_prot($codCliente);
        $userSig = sec_users_sigla($codCliente);
        atualizar_status("comentario", $phoneNumber, $coment);
        atualizar_status("data", $phoneNumber, "'" . $data . "'");
        atualizar_status("hora", $phoneNumber, $hora);
        $protocolo = numerar_protocolo($phoneNumber, $nomeAbre, $codCliente, $ativo, $problema, $coment, $codProt, $userSig, $data, $hora);
        $andamento = 0;
        atualizar_status("andamento", $phoneNumber, $andamento);
        excluir_status();
        $texto = "Chamado para ativo " . $ativo . " aberto.\n\nNúmero de protocolo: " . $protocolo . "\n\nAguarde o nosso contato";
        enviar_mensagem($phoneNumber, $texto);
        $texto = "*Esta conversa foi encerrada!*";
        enviar_mensagem($phoneNumber, $texto);
    }
    function finalizar_suprimento($phoneNumber, $data, $hora){
        $ativo = consulta_ativo_status($phoneNumber);
        $nomeAbre = consulta_nome_abre_status($phoneNumber);
        $codCliente = consulta_codcliente_status($phoneNumber);
        $codProt = sec_users_cod_prot($codCliente);
        $userSig = sec_users_sigla($codCliente);
        atualizar_status("data", $phoneNumber, "'" . $data . "'");
        atualizar_status("hora", $phoneNumber, $hora);
        
        $protocolo = numerar_protocolo_suprimento($phoneNumber, $nomeAbre, $codCliente, $ativo, $codProt, $userSig, $data, $hora);
        $andamento = 0;
        atualizar_status("andamento", $phoneNumber, $andamento);
        excluir_status();
        $texto = "Chamado para ativo " . $ativo . " aberto.\n\nNúmero de protocolo: " . $protocolo . "\n\nAguarde o nosso contato";
        enviar_mensagem($phoneNumber, $texto);
        $texto = "*Esta conversa foi encerrada!*";
        enviar_mensagem($phoneNumber, $texto);
    }
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
    function atualizar_status($campo, $numeroTelefone, $valor){
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

        if($campo == "problema"){
            $valor = "'" . $valor . "'";
        }

        if($campo == "comentario"){
            $valor = "'" . $valor . "'";
        }

        if($campo == "hora"){
            $valor = "'" . $valor . "'";
        }

        // Consulta para verificar se o ID existe
        $sql = "UPDATE status_conversa SET $campo = $valor WHERE telefone = $numeroTelefone";
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
        $sql = "INSERT INTO chamados (cod_cliente, telefone, data, hora, comentario, ativo) VALUES ($value1, $value2, $value3, $value4, $value5, $value6)";
        $result = $conn->query($sql);

        //Fechando a conexão
        $conn->close();
    }
    function abrir_chamado($numeroTelefone){
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
        $sql = "SELECT ativo, cod_cliente, comentario, data, hora FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $retorno = $row['andamento'];
        }

        //Fechando a conexão
        $conn->close();

        return $retorno;
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
        $servername = "192.168.176.140";
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

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()){
                $retorno = $row['RazaoSocia'];
            }
        }else{
            $retorno = "ERROR!";
        }

        

        //Fechando a conexão
        $conn->close();

        return $retorno;
    }
    function consultar_equipamento($ativo){
        $servername = "192.168.176.140";
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
    function numerar_protocolo($phoneNumber, $nomeAbre, $codCliente, $ativo, $defeito, $comentCli, $codProt, $userSig, $data, $hora){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT protocolo, data_abertura, numerador FROM numera_protocolo WHERE data_abertura = '$data' ORDER BY numerador DESC LIMIT 1";

        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $numera = $row['numerador'];
            }
        }else{
            $numera = 0;
        }

        for($i = 1; $i < 3; $i++) {

            $numera++;//$numera == 1

            if ($numera < 100 ){
                if ($numera < 10){
                    $numera = "00" . $numera;
                    }
                else{
                    $numera = "0" . $numera;
                    }
            }
            

            $protocolo = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);


            $sql2 = "INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador) VALUES ('$protocolo', '$data', '$hora', $numera)";
            $result =$conn->query($sql2);
        }

        $numera++;
        
        //-----------------------------------------------------------------------------------------
        $numera = $numera -2;
		if ($numera < 100 ){
			if ($numera < 10){
				$numera = "00" . $numera;
				}
			else{
				$numera = "0" . $numera;
				}
		}

	    $protocolo_i = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
        //	$solicitante = "Sistema On Line - Usuário: " . [usr_name];
	    $solicitante = $nomeAbre;
        // Incluir Protocolo Inicial no Sistema //
        
        $coment_at = " - *** NECESSITA ATENDIMENTO ***";
        
	    $comentario = "Defeito: " . $defeito . "\n$comentCli\n\n". $coment_at;
	    $conn->query("INSERT INTO historico_protocolos (prot_inicial, data_abertura, Comentario,
			hora_abre, codigo, radio_forn, assunto, executor, prev_log_in, prev_lab, 
			prev_atend, prev_fat, prev_log_out, prev_com, alteradoPor, dataalt, solicitante, ativo, fone, chamado) VALUES
			('$protocolo_i', '$data', '$comentario', '$hora', '$codCliente',
			'cli', '[assunto]', '$userSig','$data', '$data',
			'$data', '$data', '$data', '$data', '$userSig', 
			'$data', '$solicitante', '$ativo', '$phoneNumber', '{tipo_atend}')");

        // Incluir Protocolo Diario no Sistema //
        $coment = "Solicitacao de chamado via sistema. " . $comentario;
        $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, Comentario, hora_abre, codigo, radio_forn, executor, local_fisico, prev_data) VALUES (
                                            '$protocolo_i', '$protocolo', '$data', 'CLIENTE', '$coment', '$hora', '$codCliente', 'cli', '$userSig', 'ATENDIMENTO', '$data')");

        
        $descri = consultar_equipamento($ativo);
        $conn->query("INSERT INTO ativos (prot_inicial, ativo, descri) VALUES ('$protocolo_i', '$ativo', '$descri')");
        //-----------------------------------------------------------------------------------------
        
        return $protocolo_i;
    }

    function numerar_protocolo2($phoneNumber, $nomeAbre, $codCliente, $comentCli, $codProt, $userSig, $data, $hora){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT protocolo, data_abertura, numerador FROM numera_protocolo WHERE data_abertura = '$data' ORDER BY numerador DESC LIMIT 1";

        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $numera = $row['numerador'];
            }
        }else{
            $numera = 0;
        }

        for($i = 1; $i < 3; $i++) {

            $numera++;//$numera == 1

            if ($numera < 100 ){
                if ($numera < 10){
                    $numera = "00" . $numera;
                    }
                else{
                    $numera = "0" . $numera;
                    }
            }
            

            $protocolo = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);


            $sql2 = "INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador) VALUES ('$protocolo', '$data', '$hora', $numera)";
            $result =$conn->query($sql2);
        }

        $numera++;
        
        //-----------------------------------------------------------------------------------------
        $numera = $numera -2;
		if ($numera < 100 ){
			if ($numera < 10){
				$numera = "00" . $numera;
				}
			else{
				$numera = "0" . $numera;
				}
		}

	    $protocolo_i = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
        //	$solicitante = "Sistema On Line - Usuário: " . [usr_name];
	    $solicitante = $nomeAbre;
        // Incluir Protocolo Inicial no Sistema //
        
	    $comentario = $comentCli;
	    $conn->query("INSERT INTO historico_protocolos (prot_inicial, data_abertura, Comentario,
			hora_abre, codigo, radio_forn, assunto, executor, prev_log_in, prev_lab, 
			prev_atend, prev_fat, prev_log_out, prev_com, alteradoPor, dataalt, solicitante, fone, chamado) VALUES
			('$protocolo_i', '$data', '$comentario', '$hora', '$codCliente',
			'cli', 'ATENDIMENTO', '$userSig','$data', '$data',
			'$data', '$data', '$data', '$data', '$userSig', 
			'$data', '$solicitante', '$phoneNumber', '{tipo_atend}')");

        // Incluir Protocolo Diario no Sistema //
        $coment = "Solicitacao de chamado via Whatsapp. \n" . $comentario;
        $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, Comentario, hora_abre, codigo, radio_forn, executor, local_fisico, prev_data) VALUES (
                                            '$protocolo_i', '$protocolo', '$data', 'CLIENTE', '$coment', '$hora', '$codCliente', 'cli', '$userSig', 'ATENDIMENTO', '$data')");
        //-----------------------------------------------------------------------------------------
        
        return $protocolo_i;
    }

    function numerar_protocolo3($phoneNumber, $nomeAbre, $codCliente, $comentCli, $codProt, $userSig, $data, $hora){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT protocolo, data_abertura, numerador FROM numera_protocolo WHERE data_abertura = '$data' ORDER BY numerador DESC LIMIT 1";

        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $numera = $row['numerador'];
            }
        }else{
            $numera = 0;
        }


        $numera++;//$numera == 1

        if ($numera < 100 ){
            if ($numera < 10){
                $numera = "00" . $numera;
                }
            else{
                $numera = "0" . $numera;
                }
        }
            

            $protocolo = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);


            $sql2 = "INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador) VALUES ('$protocolo', '$data', '$hora', $numera)";
            $result =$conn->query($sql2);

	    $protocolo_i = "30001" . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
        //	$solicitante = "Sistema On Line - Usuário: " . [usr_name];
	    $solicitante = $nomeAbre;
        // Incluir Protocolo Inicial no Sistema //
        
	    $comentario = $comentCli;

        // Incluir Protocolo Diario no Sistema //
        $nomeEmpresa = consulta_nome_empresa_status($phoneNumber);
        $coment = "Solicitacao de chamado via Whatsapp. \nNome da Empresa: " . $nomeEmpresa . " \nSolicitante: " . $solicitante . "\nTelefone: " . $phoneNumber . "\n" .$comentario;
        $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, Comentario, hora_abre, codigo, radio_forn, executor, local_fisico, prev_data) VALUES (
                                            '$protocolo_i', '$protocolo', '$data', 'CLIENTE', '$coment', '$hora', '$codCliente', 'cli', '$userSig', 'ATENDIMENTO', '$data')");
        //-----------------------------------------------------------------------------------------
        
        return $protocolo;
    }
    function consulta_ativo_status($numeroTelefone){
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
        $sql = "SELECT ativo FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ativo = $row['ativo'];
            }
        }
        return $ativo;
    }
    function consulta_nome_abre_status($numeroTelefone){
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
        $sql = "SELECT nome_abre FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $nomeAbre = $row['nome_abre'];
            }
        }
        return $nomeAbre;
    }
    function consulta_codcliente_status($numeroTelefone){
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
        $sql = "SELECT cod_cliente FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $codCliente = $row['cod_cliente'];
            }
        }
        return $codCliente;
    }
    function consulta_problema_status($numeroTelefone){
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
        $sql = "SELECT problema FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $problema = $row['problema'];
            }
        }
        return $problema;
    }
    function consulta_comentario_status($numeroTelefone){
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
        $sql = "SELECT comentario FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $comentario = $row['comentario'];
            }
        }
        return $comentario;
    }
    function sec_users_cod_prot($codCliente){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT cod_prot FROM sec_users WHERE cod_cliente = '$codCliente'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $codProt = $row['cod_prot'];
            return $codProt;
        }
    }//Fim da function sec_users
    function sec_users_sigla($codCliente){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT sigla FROM sec_users WHERE cod_cliente = '$codCliente'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $sigla = $row['sigla'];
            return $sigla;
        }
    }
    function listar_equip($codCliente, $opcao){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT ativo FROM equip_contrato WHERE cod_unidade = '$codCliente' AND numeroserie != 'INFORLASER'";
        $result = $conn->query($sql);

        $i = 0;
        $listaEquip = "";
        $ativos = array();

        while ($row = $result->fetch_assoc()){
            $i++;
            $ativo = $row['ativo'];
            $descricao = consultar_equipamento($ativo);

            array_push($ativos, $row['ativo']);
            
            $teste = "Isso é um teste!";

            $listaEquip = $listaEquip . "*" . $i . ".* " . $ativo . " - " . $descricao . "\n";
        }
        return array($listaEquip, $ativos[$opcao]);
    }
    //-------------------------------------------------------------------------------------------------------
    function consulta_hora_status($numeroTelefone){
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
        $sql = "SELECT hora FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $horaBanco = $row['hora'];
            }
        }
        return $horaBanco;
    }
    function comparar_hora($numeroTelefone){
        date_default_timezone_set('America/Sao_Paulo');
        $hora_atual_brasilia = new DateTime(); // Obter a hora atual como um objeto DateTime

        $hora_inicial = new DateTime(consulta_hora_status($numeroTelefone)); // Hora inicial, no formato 'H:i:s'
        $minutos_a_adicionar = new DateInterval('PT5M'); // 5 minutos a adicionar (P - período, T - tempo, 5M - 5 minutos)
        
        $hora_final = $hora_inicial->add($minutos_a_adicionar);

        $tempo_limite = $hora_final;

        if ($tempo_limite < $hora_atual_brasilia) {
            $andamento = 0;
            atualizar_status("andamento", $numeroTelefone, $andamento);
            excluir_status();
            $texto = "Esse atendimento foi finalizado por que nenhuma mensagem foi enviada no período de 5 minutos.\n\nPor favor, inicie uma nova conversa.";
            enviar_mensagem($numeroTelefone, $texto);
        }
    }
    function numerar_protocolo_suprimento($phoneNumber, $nomeAbre, $codCliente, $ativo, $codProt, $userSig, $data, $hora){
        $servername = "192.168.176.140";
        $username = "root";
        $password = "je131199";
        $dbname = "protocolo";

        // Criando a conexão
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificando a conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $sql =  "SELECT protocolo, data_abertura, numerador FROM numera_protocolo WHERE data_abertura = '$data' ORDER BY numerador DESC LIMIT 1";

        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $numera = $row['numerador'];
            }
        }else{
            $numera = 0;
        }

        $numera++;

        for($i = 1; $i < 4; $i++) {
            if ($numera < 100 ){
                if ($numera < 10){
                    $numera = "00" . $numera;
                    }
                else{
                    $numera = "0" . $numera;
                    }
            }
            
            $protocolo1 = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
            $protocolo = $codProt . $numera -1 . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
            $protatend = "17" . $numera +1 . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);

            $result =$conn->query("INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador)VALUES ('$protocolo1', '$data', '$hora','$numera')");
            
            $numera++;
        }
        $numera2 = $numera;

        $conn->query("INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador)VALUES ('$protatend', '$data', '$hora','$numera2')");
        
        $numera = $numera - 3;
		if ($numera < 100 ){
			if ($numera < 10){
				$numera = "00" . $numera;
				}
			else{
				$numera = "0" . $numera;
				}
		}

	    $protocolo_i = $codProt . $numera . substr($data,2,2) . substr($data,8,2) . substr($data,5,2);
        //	$solicitante = "Sistema On Line - Usuário: " . [usr_name];
	    $solicitante = $nomeAbre;
        // Incluir Protocolo Inicial no Sistema //
        
        $coment_at = " - *** NECESSITA ATENDIMENTO ***";
        
	    $comentario = "Solicitacao de Suprimento";
	    $conn->query("INSERT INTO historico_protocolos (prot_inicial, data_abertura, Comentario,
			hora_abre, codigo, radio_forn, assunto, executor, prev_log_in, prev_lab, 
			prev_atend, prev_fat, prev_log_out, prev_com, alteradoPor, dataalt, solicitante, ativo, fone, chamado) VALUES
			('$protocolo_i', '$data', '$comentario', '$hora', '$codCliente',
			'cli', '[assunto]', '$userSig','$data', '$data',
			'$data', '$data', '$data', '$data', '$userSig', 
			'$data', '$solicitante', '$ativo', '$phoneNumber', '{tipo_atend}')");

        $coment = "Solicitacao de cartucho via sistema. " . $comentario;

        //*CEP - LOGISTICA 
        $result = $conn->query("SELECT Cep FROM Clientes WHERE Cod_Cliente = '$codCliente'");
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $fatorcep = substr($row['Cep'],0,2);
            }
        }
        
        $diaabre = date('w');
        switch($fatorcep){
            case "01":
                $diaentr = 1;
                break;
            case "02":
                $diaentr = 3;
                break;
            case "03":
                $diaentr = 3;
                break;
            case "04":
                $diaentr = 1;
                break;
            case "05":
                $diaentr = 4;
                break;
            case "06":
                $diaentr = 4;
                break;
            case "07":
                $diaentr = 3;
                break;
            case "08":
                $diaentr = 5;
                break;
            case "09":
                $diaentr = 5;
                break;
            default:
                $diaentr = 1;
        }

        if ($diaabre < $diaentr){
            $correcao = $diaentr - $diaabre;
            $diacorreto = new DateTime($data);
    //        $diacorreto = sc_date(date('Ymd'), "aaaammdd", "+",$correcao, 0, 0);
            $diacorreto->add(new DateInterval('P' . $correcao . 'D'));
    //		$diacorreto = date('d/m/Y', strtotime('+$diaentr days'));
            
        }
        elseif ($diaabre == $diaentr){
            $diasASomar = 7;
            $diacorreto = new DateTime($data);
    //        $diacorreto = sc_date(date('Ymd'), "aaaammdd", "+",7, 0, 0);
            $diacorreto->add(new DateInterval('P' . $diasASomar . 'D'));
    //		$diacorreto = date('d/m/Y', strtotime('+7 days'));
        }
        elseif ($diaabre > $diaentr){
            $correcao = $diaabre - $diaentr;
            $fator = 7 - $correcao;
            $diacorreto = new DateTime($data);
    //        $diacorreto = sc_date(date('Ymd'), "aaaammdd", "+",$fator, 0, 0);
            $diacorreto->add(new DateInterval('P' . $fator . 'D'));
    //		$diacorreto = date('d/m/Y', strtotime('+$fator days'));
        }

        $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, 
        Comentario, codigo, radio_forn, executor, local_fisico, prev_data, prot_logistica) VALUES (
        '$protocolo_i', '$protocolo', '$data', 'CLIENTE', '$coment', 
        '$codCliente', 'cli', '$userSig', 'ATENDIMENTO', '$data', '$protatend')");

        //INSERIR O PROTOCOLO PARA ESTOQUE 

        $fator_prep = $diaentr - 1;
        if ($fator_prep == 0){
            $diasASomar = 3;
            $diasepara = $diacorreto;
            $diasepara->add(new DateInterval('P' . $diasASomar . 'D'));
        }
        elseif ($fator_prep < 6) {
            $diasASomar = 1;
            $diasepara = $diacorreto;
            $diasepara->add(new DateInterval('P' . $diasASomar . 'D'));
        }

        $diasepara_str = $diasepara->format('Y-m-d');

        $result = $conn->query("SELECT protocolo, data_abertura, numerador FROM numera_protocolo WHERE data_abertura = '$diasepara_str' ORDER BY numerador");

        if (mysqli_num_rows($result) == 0) {
            $numera = 0;
        } else {
            // Move o ponteiro do resultado para o último registro
            mysqli_data_seek($result, mysqli_num_rows($result) - 1);
        
            // Obtém os dados do último registro
            $row = mysqli_fetch_row($result);
        
            // Atribui o valor do terceiro campo (índice 2) na variável $numera
            $numera = $row[2];
        }

        $numera++;

		if ($numera < 100 ){
			if ($numera < 10){
				$numera_str = "00" . $numera;
				}
			else{
				$numera_str = "0" . $numera;
				}
		}
		$protestoque = "10" . $numera_str . substr($diasepara_str,2,2). substr($diasepara_str,8,2) . substr($diasepara_str,5,2);

		$result = $conn->query("INSERT INTO numera_protocolo (protocolo, data_abertura, 
					hora, numerador)VALUES ('$protestoque', '$diasepara_str', '$hora',
					$numera)");
		
		$acao_est = 'LIBERAR SUPRIMENTO - ' . $comentario;
	    $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, 
				Comentario, codigo, radio_forn, executor, local_fisico, prev_data, prot_logistica) VALUES (
				'$protocolo_i', '$protatend', '$data', 'ATENDIMENTO', '$acao_est', 
				'$codCliente', 'cli', '$userSig', 'ESTOQUE', '$diasepara_str', '$protestoque')");

        $coment2 = "Entrega de suprimento (pre-agendada pelo sistema) Ativo: $ativo";

        $diacorreto_str = $diacorreto->format('Y-m-d');

        $conn->query("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, 
                    Comentario, codigo, radio_forn, executor, local_fisico, prev_data) VALUES (
                    '$protocolo_i', '$protocolo1', '$data', 'ATENDIMENTO', '$coment2', 
                    '$codCliente', 'cli', '$userSig', 'LOGISTICA', '$diacorreto_str')");

        $descri = consultar_equipamento($ativo);
        $conn->query("INSERT INTO ativos (prot_inicial, ativo, descri) VALUES ('$protocolo_i', '$ativo', $descri)");

        return $protocolo_i;
    }
    //--------------------------------------------------------------------------------------------------------
    function consulta_nome_empresa_status($numeroTelefone){
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
        $sql = "SELECT nome_empresa FROM status_conversa WHERE telefone = $numeroTelefone";
        $result = $conn->query($sql);

        // Fechando a conexão
        $conn->close();

        // Verificando o resultado da consulta
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $nomeEmpresa = $row['nome_empresa'];
            }
        }
        return $nomeEmpresa;
    }
?>