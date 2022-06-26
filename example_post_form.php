<?php	
	ini_set('display_errors',1);
	
	include_once 'Emailer.php';
	
	$fromEmail 		= 'mail@sachinpuri.com';
	$fromName 		= 'Sachin Puri';
	$to 			= $_POST['to'];
	$cc 			= $_POST['cc'];
	$bcc 			= $_POST['bcc'];
	$subject 		= $_POST['subject'];
	$message 		= $_POST['message'];
	$attachments 	= [];
	$size 			= 0;
	$type 			= '';
	$name 			= '';	
	
	// Upload all attachments to server
	if(count($_FILES) > 0){
		foreach($_FILES as $attachment){
			$tmp_name = $attachment['tmp_name'];
			$name = $attachment['name'];
			$size = $attachment['size'];
			$type = $attachment['type'];
			if(move_uploaded_file($tmp_name, $name)){
				$attachments[] = $name;
			}	
		}		
	}
	
	// Validate
	if(strlen($to)==0){
		error("To email address is blank");
	}elseif(strlen($subject)==0){
		error("Subject is blank");
	}elseif(strlen($message)==0){
		error("Message is blank");
	}else{		
	
		$emailer = new Emailer();
		$emailer->to($to)
				->from($fromEmail, $fromName)
				->subject($subject)
				->message($message);
				
		foreach($attachments as $attachment){			
			$emailer->attachment($attachment);
		}
				
		if($emailer->send()){
			response("Email sent sucessfully");	
		}else{
			error("Error " . error_get_last()['message']);
		}
	}
	
	function error($message){
		header("Content-Type: application/json");
		$response = [
			'status' => 0,
			'message' => 'error',
			'data' => $message
		];
		echo json_encode($response);
	}
	
	function response($message){
		header("Content-Type: application/json");
		$response = [
			'status' => 1,
			'message' => 'Message successfully sent',
			'data' => $message
		];
		echo json_encode($response);
	}
?>