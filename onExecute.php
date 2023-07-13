<?php
include 'enviar_teste.php';
	$data = json_decode(file_get_contents('php://input'), true);

	//ESTOU RECEBENDO A MENSAGEM E A MENSAGEM É DO TIPO CHAT
	//if( ($data['messages'][0]['fromMe'] === false) && ($data['messages'][0]['type'] == "chat") ) {
		
		$nome = $data['messages'][0]['senderName'];
		$phone = explode("@", $data['messages'][0]['chatId'])[0];
		$body = $data['messages'][0]['body'];
		
		enviar_teste();
		echo $phone;
	//}
	
?>