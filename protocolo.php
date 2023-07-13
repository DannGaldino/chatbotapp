<?php
    function sec_users($codCliente){
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

        $sql =  "SELECT cod_prot FROM sec_users WHERE cod_cliente = '$codCliente";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()){
            $codProt = $row['cod_prot'];
        }
        return $codProt;
    }//Fim da function sec_users

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
        $numera = $row['numerador'];
    }else{
        $numera = 0;
    }

    $numera++;

    for($i = 1; $i < 3; $i++) {
        if ($numera < 100 ){
            if ($numera < 10){
                $numera = "00" . $numera;
                }
            else{
                $numera = "0" . $numera;
                }
        }

        $protocolo = sec_users($codCliente) . $numera . substr($data,2,2) . substr($data,6,2) . substr($data,4,2);

        
        $sql2 = "INSERT INTO numera_protocolo (protocolo, data_abertura, hora, numerador)VALUES ('$protocolo', '$data', '$hora','$numera')";
        $conn->query($sql2);
        $numera++;
    }
    $numera = $numera -2;

    if ($numera < 100 ){
        if ($numera < 10){
            $numera = "00" . $numera;
            }
        else{
            $numera = "0" . $numera;
            }
    }

    $protocolo_i = sec_users($codCliente) . $numera . substr($data, 2, 2) . substr($data_abre, 6, 2) . substr($data, 4, 2);
    
    //-----------------------------------------------------------------------------------------

    /*$sql3 = "SELECT VALUES ";
    $conn->query($sql2);
    $numera++;

    {prot_inicial} = $protocolo_i;//insert
//	$solicitante = "Sistema On Line - Usuário: " . [usr_name];
    $solicitante = {nome_abre};
// Incluir Protocolo Inicial no Sistema //
    if ({tipo_atend} == 'S') { 
            $coment_at = " - *** FAZER SUPORTE REMOTO ***";
        }
    else {
            $coment_at = " - *** NECESSITA ATENDIMENTO NO LOCAL ***";
    }
    [comentario] = [comentario] . {Defeito} . $coment_at;
    sc_exec_sql("INSERT INTO historico_protocolos (prot_inicial, data_abertura, Comentario, hora_abre, codigo, radio_forn, assunto, executor, prev_log_in, prev_lab, prev_atend, prev_fat, prev_log_out, prev_com, alteradoPor, dataalt, solicitante, ativo, fone, chamado) VALUES('$protocolo_i', '$data_abre', '[comentario]', '$hora_abre', '[usr_cli]','cli', '[assunto]', '[usr_sig]','$data_abre', '$data_abre', '$data_abre', '$data_abre', '$data_abre', '$data_abre', '[usr_sig]', '$data_abre', '$solicitante', '{ativo}', '{telefone}', '{tipo_atend}')");

// Incluir Protocolo Diario no Sistema //
    $coment = "Solicitação de chamado via sistema. " . [comentario];
    sc_exec_sql("INSERT INTO prot_diario (prot_inicial, protocolo, data_abertura, local_abre, 
                Comentario, codigo, radio_forn, executor, local_fisico, prev_data) VALUES (
                '$protocolo_i', '$protocolo', '$data_abre', 'CLIENTE', '$coment', 
                '[usr_cli]', 'cli', '[usr_sig]', 'ATENDIMENTO', '$data_abre')");

// variaveis

    sc_set_global($coment);
    sc_set_global($protocolo);
    sc_set_global($protocolo_i);*/
?>