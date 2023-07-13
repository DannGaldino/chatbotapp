<?php
$data = json_decode(file_get_contents('php://input'), true);

//ESTOU RECEBENDO A MENSAGEM E A MENSAGEM É DO TIPO CHAT
if( ($data['messages'][0]['fromMe'] === false) && ($data['messages'][0]['type'] == "chat") ) {
	
	$nome = $data['messages'][0]['senderName'];
	$phone = explode("@", $data['messages'][0]['chatId'])[0];
	$body = $data['messages'][0]['body'];
	
	if(status_conversa($phone)) {//COM CONVERSA
		
		if(strtoupper($body) == "PEDIDO") {
			salva_historico($phone, '', '', 'O cliente decidiu iniciar o pedido.', '001');
			if(verifica_cliente($phone)){
				salva_historico($phone, '', '', 'Enviamos as opções iniciais para o cliente.', '003');
				$message = $nome.", agora vamos começar a anotar o seu pedido.\nEscolha uma das opções para começar:\n*1* - Fazer pedido";
				enviar_mensagem($phone, $message);
			} else {
				salva_historico($phone, '', '', 'Solicitamos o nome do cliente.', '002');
				$message = "Notamos que você não esta cadastrado em nosso sistema, por favor, nos informe o seu nome:";
				enviar_mensagem($phone, $message);
			}
		} else {
			
			$ultimo = ultimo_codigo($phone);
			switch($ultimo) {
				case 2:
					$name = $body;
					salva_historico($phone, '', '', 'Capturamos o nome do cliente.', '004');
					cadastra_cliente($name, $phone);
					
					salva_historico($phone, '', '', 'Enviamos as opções iniciais para o cliente.', '003');
					$message = $name.", agora vamos começar a anotar o seu pedido.\nEscolha uma das opções para começar:\n*1* - Fazer pedido";
					enviar_mensagem($phone, $message);
					break;
				case 3:
					if($body == 1) {
						salva_historico($phone, '', '', 'Enviamos os produtos para o cliente.', '005');
						$produtos = lista_produtos();
						$message = "Informe o código do produto desejado:\n\n".$produtos;
						enviar_mensagem($phone, $message);
					} else {
						$message = "Você enviou uma opção inválida, tente novamente.";
						enviar_mensagem($phone, $message);
					}
					break;
				case 5:
					$produto = $body;
					if(verifica_produto($produto)) {
						$pedido = cria_pedido($phone, $produto);
						salva_historico($phone, $pedido, $produto, 'O cliente escolheu produto.', '006');
						
						$message = "Pedido criado com sucesso, pedido número: ".$pedido;
						enviar_mensagem($phone, $message);
					} else {
						$message = "Você enviou uma opção inválida, tente novamente.";
						enviar_mensagem($phone, $message);
					}
					break;
			}
		}

	} else {//SEM CONVERSA
		salva_historico($phone, '', '', 'Mensagem de boas vindas.', '000');
		$message = "Olá, seja bem vindo ao nossa atendimento virtual. Para iniciar o seu pedido, digite a palavra *PEDIDO*.";
		enviar_mensagem($phone, $message);
	}
	
}