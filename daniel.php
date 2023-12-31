<?php
    $data1 = file_get_contents("php://input");
    $event = json_decode($data1, true);
    $phoneNumber = substr($event['key']['remoteJid'], 0, 13);
    $dataType = $event['messageType'];
    $mensagem_recebida = $event['message'][$dataType];
    $messageTimeStamp = $event['messageTimestamp'];
    date_default_timezone_set('America/Sao_Paulo');
    $data = substr(date('Y-m-d H:i:s', $messageTimeStamp), 0, 10);
    $hora = substr(date('d-m-Y H:i:s', $messageTimeStamp), 11);

    if($event['messageType'] != 'message.ack' && $event['key']['fromMe'] === false){
        if (verifica_cliente($phoneNumber) === false){
            $texto = "Ola, seja bem vindo ao atendimento da Inforlaser\n\nPor favor, nos informe seu nome para darmos início ao atendimento";
            enviar_mensagem($phoneNumber, $texto);
            $mensagem_recebida = "";
            $andamento = 1;
            iniciar_status($phoneNumber, $andamento);
        }

        switch (verificar_status($phoneNumber)){
            case 1:
                if($mensagem_recebida != ""){
                    $nomeAbre = "'" . $mensagem_recebida . "'";
                    atualizar_status("nome_abre", $phoneNumber, $nomeAbre);
                    $texto = "Por favor, nos informe se você já é um cliente da Inforlaser\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 2;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }
                break;
            case 2:
                if($mensagem_recebida == "1"){
                    $texto = "Nos informe o seu código de cliente por favor ";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 3;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                }else{
                    $texto = "Por favor, nos informe se você já é um cliente da Inforlaser\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                    enviar_mensagem($phoneNumber, $texto);
                }
                break;
            case 3:
                if ($mensagem_recebida != ""){
                    $codCliente = $mensagem_recebida;
                    if(consultar_razaoSocial($codCliente) != ""){
                        $texto = "Você representa a empresa " . consultar_razaoSocial($mensagem_recebida) . "?\n\nEscolha uma das opções abaixo:\n*1*. Sim\n*2*. Não";
                        enviar_mensagem($phoneNumber, $texto);
                        $andamento = 4;
                        atualizar_status("andamento", $phoneNumber, $andamento);
                        atualizar_status("cod_cliente", $phoneNumber, $codCliente);
                    }else{
                        $texto = "Cliente não encontrado!!!\n\nPor favor, verifique se o codigo informado está correto e tente novamente!";
                        enviar_mensagem($phoneNumber, $texto);
                    }
                }
                break;
            case 4:
                if($mensagem_recebida == "1"){
                    $texto = "Qual serviço você deseja solicitar?\n\nEscolha uma das opções abaixo:\n*1*. Chamado\n*2*. Suprimento";
                    enviar_mensagem($phoneNumber, $texto);
                    $andamento = 5;
                    atualizar_status("andamento", $phoneNumber, $andamento);
                    $mensagem_recebida = "";
                }
                if($mensagem_recebida == "2"){
                    $texto = "Nos informe o seu código de cliente por favor ";
                    enviar_mensagem($phoneNumber, $texto);
                    $mensagem_recebida = "";
                    $andamento = 3;
                    atualizar_status("andamento", $phoneNumber, $andamento);
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
                }
                if($mensagem_recebida == "2"){
                    $texto = "Teste";
                    enviar_mensagem($phoneNumber, $texto);
                    $andamento = 5;
                    atualizar_status("andamento",$phoneNumber, $andamento);
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
                }
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
        $texto = "Chamado para ativo " . $ativo . " aberto.\n\nNúmero de protocolo: " . $protocolo;
        enviar_mensagem($phoneNumber, $texto);
        $texto = "*Atendimento Finalizado*";
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
    function numerar_protocolo($phoneNumber, $nomeAbre, $codCliente, $ativo, $defeito, $comentCli, $codProt, $userSig, $data, $hora){
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
        $coment = "Solicitação de chamado via sistema. " . $comentario;
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

        $sql =  "SELECT cod_prot FROM sec_users WHERE cod_cliente = '$codCliente'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $codProt = $row['cod_prot'];
            return $codProt;
        }
    }//Fim da function sec_users

    function sec_users_sigla($codCliente){
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

        $sql =  "SELECT sigla FROM sec_users WHERE cod_cliente = '$codCliente'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $sigla = $row['sigla'];
            return $sigla;
        }
    }

    function listar_equip($codCliente, $opcao){
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
?>