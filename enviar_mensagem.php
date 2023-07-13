$curl = curl_init();
$data = array(
	"phone" => $numero,
	"body" => $conteudo,
);
$data = json_encode($data);
curl_setopt_array($curl, array(
CURLOPT_URL => "http://api5.megaapi.com.br/rest/sendmMssage/megaapi-MZAyU7l7QPMtYZE90fDNATF0b1",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => $data,
	CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Content-Type: application/json"
		),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
	echo "Response sendmessage: ".$response;
} else {
	echo "Response sendmessage: ".$response;
}