<?php
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
    $sql = "SELECT andamento FROM status_conversa WHERE telefone = '5511993383537'";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()){
        $retorno = $row['andamento'];
    }

    //Fechando a conexão
    $conn->close();

    echo $result;
    
?>