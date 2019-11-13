<?php 

namespace App\Libraries;

class SendNotifLib{

	public function send_telegram_message($chat_id, $message)
	{
		$TOKEN  = "499271668:AAHDVOjfsKVW4dF92x02FhTeHCGsYWR74x4";  
		$method = "sendMessage";
      	$url    = "https://api.telegram.org/bot" . $TOKEN . "/". $method;
      
		$header = [
			"X-Requested-With: XMLHttpRequest",
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
		];
		$post = [
		  'chat_id' => $chat_id,
		 'parse_mode' => 'html', // aktifkan ini jika ingin menggunakan format type HTML, bisa juga diganti menjadi Markdown
		  'text' => $message
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_REFERER, $refer);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$datas = curl_exec($ch);
		$error = curl_error($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// \dd($datas);

		return $status;
		
	}

}

 ?>