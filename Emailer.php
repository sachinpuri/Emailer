<?php
	class Emailer {
		
		private $to 			= '';
		private $fromEmail		= '';
		private $fromName		= '';
		private $subject		= '';
		private $cc				= '';
		private $bcc			= '';
		private $message		= '';
		private $attachments 	= [];
		private $boundary		= '';
		private $headers		= '';
		private $body			= '';
		private $allowedExt		= ['jpg', 'jpeg', 'gif', 'png', 'doc', 'pdf'];
		private $allowedMime	= ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/pdf'];
		
		function __construct(){
			$this->boundary = md5("random");
		}
		
		function to($to){
			$this->to = $to;
			return $this;
		}
		
		function from($fromEmail, $fromName = ''){
			$this->fromEmail = $fromEmail;
			$this->fromName = $fromName;
			return $this;
		}
		
		function cc($cc){
			$this->cc = cc;
			return $this;
		}
		
		function bcc($bcc){
			$this->bcc = bcc;
			return $this;
		}
		
		function subject($subject){
			$this->subject = $subject;
			return $this;
		}
		
		function message($message){
			$this->message = $message;
			return $this;
		}
		
		function attachment($attachment){
			$this->attachments[] = $attachment;
			return $this;
		}
		
		private function headers(){
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-Type: multipart/mixed; boundary = ' . $this->boundary;
			$headers[] = 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>';
			if(strlen($this->cc) > 0) $headers[] = 'Cc: ' . $this->cc;	
			if(strlen($this->bcc) > 0) $headers[] = 'Bcc: ' . $this->bcc;
			$headers[] = 'boundary = ' . $this->boundary;
			
			$this->headers = implode("\r\n", $headers);
		}
		
		private function body(){
			$body[] = "--$this->boundary";
			$body[] = "Content-Type: text/html; charset=UTF-8";
			$body[] = "Content-Transfer-Encoding: base64\r\n";
			$body[] = chunk_split(base64_encode($this->message));
			
			if(count($this->attachments) > 0){
				foreach($this->attachments as $attachment){
					$pathinfo = pathinfo($attachment);					
					$name = $pathinfo['filename'];
					$ext = $pathinfo['extension'];
					$type = mime_content_type($attachment);					
					
					if(in_array($ext, $this->allowedExt) && in_array($type, $this->allowedMime)){
						$size = filesize($attachment);
						
						$handle = fopen($attachment, "r");
						$content = fread($handle, $size);
						fclose($handle); 
					
						$encoded_content = chunk_split(base64_encode($content));
						
						$body[] = "--$this->boundary";
						$body[] = "Content-Type: $type; name=".$name;
						$body[] = "Content-Disposition: attachment; filename=".$name;
						$body[] = "Content-Transfer-Encoding: base64";
						$body[] = "X-Attachment-Id: ".rand(1000, 99999)."\r\n";
						$body[] = $encoded_content; // Attaching the encoded file with email	
					}
				}				
			}				
			
			$this->body = implode("\r\n", $body);
		}
		
		function send(){
			$this->headers();
			$this->body();
			
			return @mail($this->to, $this->subject, $this->body, $this->headers);				
		}
		
	}