<?php

// IMPORTANT!!!  This is an ABSTRACT class to be extended.  DO NOT declare instances of it, as they
// will not function.  Instead, create subclasses which implement the sendMail() function.
class AbstractMail {
	
	var $assoc_strTo = array();
	var $stringFromAddress;
	var $stringFromName;
	var $stringReplyTo;
	/*var $assoc_strCc;
	var $assoc_strBcc;*/
	var $stringSubject;
	var $stringMessage;
	
	function AbstractMail() {
		
	}
	
	function addRecipient( $strRecipientAddress ) {
		$this->assoc_strTo[ $strRecipientAddress ] = $strRecipientAddress;
	}
	
	function removeRecipient( $strRecipientAddress ) {
		unset( $this->assoc_strTo[ $strRecipientAddress ] );
	}
	
	function setSubject( $strSub ) {
		$this->stringSubject = $strSub;
	}
	
	function setMessage( $strMsg ) {
		$this->stringMessage = $strMsg;
	}
	
	function setFromAddress( $strFromAddress ) {
		$this->stringFromAddress = $strFromAddress;
	}
	
	function setFromName( $strFromName ) {
		$this->stringFromName = $strFromName;
	}
	
	function setReplyAddress( $strReplyAddress ) {
		$this->stringReplyTo = $strReplyAddress;
	}
	/*
	function addCcRecipient( $strCcAddress ) {
		$this->assoc_strCc[ $strCcAddress ] = $strCcAddress;
	}
	
	function removeCcRecipient( $strCcAddress ) {
		unset( $this->assoc_strTo[ $strCcAddress ] );
	}
	
	function addBccRecipient( $strBccAddress ) {
		$this->assoc_strBcc[ $strBccAddress ] = $strBccAddress;
	}
	
	function removeBccRecipient( $strBccAddress ) {
		unset( $this->assoc_strTo[ $strBccAddress ] );
	}
	*/
	
	function getRecipients() {
		return array_values( $this->assoc_strTo );
	}
	
	function getSubject() {
		return $this->stringSubject;
	}
	
	function getMessage() {
		return $this->stringMessage;
	}
	
	function getFromAddress() {
		return $this->stringFromAddress;
	}
	
	function getFromName() {
		return $this->stringFromName;
	}
	
	function getReplyAddress() {
		return $this->stringReplyTo;
	}
	/*
	function getCcRecipients() {
		return array_values( $this->assoc_strCc );
	}
	
	function getBccRecipients() {
		return array_values( $this->assoc_strBcc );
	}
	*/
	function sendMail() {
		// UNIMPLEMENTED IN THIS BASE CLASS!!!
	}
}




class PlainTextMail extends AbstractMail {
	
	function PlainTextMail() {
		
	}
	
	function sendMail() {
		
		if( $this->assoc_strTo ) {
			$to = implode( ', ', $this->assoc_strTo );
			
                        $headers = "";

			if( $this->stringSubject ) $subject = $this->stringSubject;
			else $subject = '';
			
			if( $this->stringMessage ) $message = $this->stringMessage;
			else $message = '';
			
			if( $this->stringFromAddress && $this->stringFromName ) {
				$headers .= "From:".$this->stringFromName."<".$this->stringFromAddress.">\n";
				ini_set( 'sendmail_from', $this->stringFromAddress );
			}
			else if( $this->stringFromAddress ) {
				$headers .= "From:<".$this->stringFromAddress.">\n";
				ini_set( 'sendmail_from', $this->stringFromAddress );
			}
			else if( $this->stringFromName ) {
				$headers .= "From:<".$this->stringFromName.">\n";
				ini_set( 'sendmail_from', '' );
			}
			
			if( $this->stringReplyTo ) $headers .= "Reply-To:<".$this->stringReplyTo.">\n";
			/*
			if( $this->assoc_strCc ) {
				$headers .= 'Cc:'.implode( ',', $this->assoc_strCc );
			}
			if( $this->assoc_strBcc ) $headers .= ' Bcc:'.implode( ',', $this->assoc_strBcc );
			*/
			
			$return = mail( $to, $subject, $message, $headers );
			ini_restore( 'sendmail_from' );
			return $return;
		}
		else return false;
	}
}




class CampusPlainTextMail extends PlainTextMail {
	
	function CampusPlainTextMail() {
		
	}
	
	function verifyAddress( $strAddress ) {
		list( $username, $junk ) = explode( '@', $strAddress );
		$ldap = new WoosterLDAP();
		$return = $ldap->verifyUsername( $username );
		if( $return ) return true;
		return false;
	}
	
	function sendMail() {
		
		if( $this->assoc_strTo ) {
			foreach( $this->assoc_strTo as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_to[ $key ] = $value;
					unset( $this->assoc_strTo[ $key ] );
				}
			}
		}
		/*
		if( $this->assoc_strCc ) {
			foreach( $this->assoc_strCc as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_cc[ $key ] = $value;
					unset( $this->assoc_strCc[ $key ] );
				}
			}
		}
		
		if( $this->assoc_strBcc ) {
			foreach( $this->assoc_strBcc as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_bcc[ $key ] = $value;
					unset( $this->assoc_strBcc[ $key ] );
				}
			}
		}
		*/
		if( $failed_to ) $failed[ 'To' ] = $failed_to;
		/*
		if( $failed_cc ) $failed[ 'Cc' ] = $failed_cc;
		if( $failed_bcc ) $failed[ 'Bcc' ] = $failed_bcc;
		*/
		if( parent::sendMail() && !$failed ) return true;
		else return $failed;
	}
}




class MimeMail extends AbstractMail {
	
	var $assoc_MimeContent_Attachments = array();
	
	function MimeMail() {
		
	}
	
	function addAttachment( $strName, $MimeContent_content ) {
		$this->assoc_MimeContent_Attachments[ $strName ] = $MimeContent_content;
	}
	
	function removeAttachment( $strName ) {
		unset( $this->assoc_MimeContent_Attachments[ $strName ] );
	}
	
	function sendMail() {
		
		if( $this->assoc_strTo ) {
			$to = implode( ', ', $this->assoc_strTo );
			
			if( $this->stringSubject ) $subject = $this->stringSubject;
			else $subject = '';
			
			if( $this->stringMessage ) $message = $this->stringMessage;
			else $message = '';
			
			if( $this->stringFromAddress ) ini_set( 'sendmail_from', $this->stringFromAddress );
			else if( $this->stringReplyTo ) ini_set( 'sendmail_from', $this->stringReplyTo );
			else ini_set( 'sendmail_from', '' );
			
			$theHeader = new MimeHeader();
			$theHeader->setFrom( $this->stringFromAddress, $this->stringFromName );
			$theHeader->setReplyTo( $this->stringReplyTo );
			
			$boundary = $theHeader->getBoundary();
			
			$theBody = new MimeBody( $boundary, $message );
			
			$message = $theBody->toString();
			
			//echo '<br>Number of attachments: '.sizeof( $this->assoc_MimeContent_Attachments ).'<br>';
			
			foreach( $this->assoc_MimeContent_Attachments as $key => $value ) {
				$message .= "--".$boundary."\n";
				$attachment = new Base64MimeAttachment( $key, $value );
				$message .= $attachment->toString()."\n\n";
			}
			
			$message .= "--".$boundary."--";
			
			$return = mail( $to, $subject, $message, $theHeader->toString() );
			
			//echo '<form name="blah"><textarea cols=100 rows=30 wrap=soft>'.$theHeader->toString().$message.'</textarea></form>';
			
			ini_restore( 'sendmail_from' );
			return $return;
		}
		else return false;
	}
}




class CampusMimeMail extends MimeMail {
	
	function CampusMimeTextMail() {
		
	}
	
	function verifyAddress( $strAddress ) {
		list( $username, $junk ) = explode( '@', $strAddress );
		$ldap = new WoosterLDAP();
		$return = $ldap->verifyUsername( $username );
		if( $return ) return true;
		return false;
	}
	
	function sendMail() {
		
		if( $this->assoc_strTo ) {
			foreach( $this->assoc_strTo as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_to[ $key ] = $value;
					unset( $this->assoc_strTo[ $key ] );
				}
			}
		}
		/*
		if( $this->assoc_strCc ) {
			foreach( $this->assoc_strCc as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_cc[ $key ] = $value;
					unset( $this->assoc_strCc[ $key ] );
				}
			}
		}
		
		if( $this->assoc_strBcc ) {
			foreach( $this->assoc_strBcc as $key => $value ) {
				if( !$this->verifyAddress( $value ) ) {
					$failed_bcc[ $key ] = $value;
					unset( $this->assoc_strBcc[ $key ] );
				}
			}
		}
		*/
		if( $failed_to ) $failed[ 'To' ] = $failed_to;
		/*
		if( $failed_cc ) $failed[ 'Cc' ] = $failed_cc;
		if( $failed_bcc ) $failed[ 'Bcc' ] = $failed_bcc;
		*/
		if( parent::sendMail() && !$failed ) return true;
		else return $failed;
	}
}




class MimeHeader {
	
	var $stringBoundary;
	var $stringContentType = 'multipart/mixed';
	var $stringFrom = FALSE;
	var $stringReplyTo = FALSE;
	
	function MimeHeader( $strBoundary = FALSE ) {
		
		if( $strBoundary ) $this->stringBoundary = $strBoundary;
		else $this->stringBoundary = '_Multipart_Boundary_['.md5( time() ).']';
		
	}
	
	function getBoundary() {
		return $this->stringBoundary;
	}
	
	function getContentType() {
		return $this->stringContentType;
	}
	
	function getFromLine() {
		return $this->stringFrom;
	}
	
	function getReplyToLine() {
		return $this->stringReplyTo;
	}
	
	function setBoundary( $strBoundary = FALSE ) {
		
		if( $strBoundary ) $this->stringBoundary = $strBoundary;
		else $this->stringBoundary = '_Multipart_Boundary_['.md5( time() ).']';
	}
	
	function setContentType( $strContentType ) {
		$this->stringContentType = $strContentType;
	}
	
	function setFrom( $strAddress, $strName = FALSE ) {
		if( $strName ) $this->stringFrom = "From: ".trim( $strName )." <".$strAddress.">\n";
		else $this->stringFrom = "From: <".$strAddress.">\n";
	}
	
	function setReplyTo( $strAddress ) {
		$this->stringReplyTo = "Reply-To: <".trim( $strAddress ).">\n";
	}
	
	function removeFrom() {
		$this->stringFrom = FALSE;
	}
	
	function toString() {
		$myString  = "MIME-Version: 1.0\n";
		
		if( $this->stringFrom ) $myString .= $this->stringFrom;
		if( $this->stringReplyTo ) $myString .= $this->stringReplyTo;
		
		$myString .= "Content-type: ".$this->stringContentType."; boundary=".$this->stringBoundary."\n";
		
		return $myString;
	}
}




class MimeBody {
	
	var $stringBoundary;
	var $stringMessage;
	
	function MimeBody( $strBoundary, $strMessage ) {
		$this->stringBoundary = $strBoundary;
		$this->stringMessage = $strMessage;
	}
	
	function getBoundary() {
		return $this->stringBoundary;
	}
	
	function getMessage() {
		return $this->stringMessage;
	}
	
	// be very careful if you're going to use this function
	function setBoundary( $strBoundary ) {
		$this->stringBoundary = $strBoundary;
	}
	
	function setMessage( $strMessage ) {
		$this->stringMessage = $strMessage;
	}
	
	function toString() {
		
		$myString  = "This is a multi-part message in MIME format.  If you are seeing this message, then your email client is not MIME-compliant.\n\n";
		$myString .= "--".$this->stringBoundary."\n";
		$myString .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
		$myString .= "Content-Transfer-Encoding: 7bit\n\n";
		$myString .= $this->stringMessage."\n";
		
		return $myString;
	}
}




// IMPORTANT!!!  This is an ABSTRACT class to be extended.  DO NOT declare instances of it, as they
// will not function.  Instead, create subclasses which implement the getEncodedContent() function.
class AbstractMimeAttachment {
	
	var $stringName;
	var $MimeContent_theContent;
	var $stringEncoding;
	
	function MIMEAttachment( $strName, $MimeContent_content, $strEncoding ) {
		setAll( $strName, $MimeContent_content, $strEncoding );
	}
	
	// this makes it easier for the subclasses to call the same function for the constructor
	// as the parent in PHP 4
	function setAll( $strName, $MimeContent_content, $strEncoding ) {
		$this->stringName = $strName;
		$this->MimeContent_theContent = $MimeContent_content;
		$this->stringEncoding = $strEncoding;
	}
	
	function getName() {
		return $this->stringName;
	}
	
	function getMimeContent() {
		return $this->MimeContent_theContent;
	}
	
	function getEncoding() {
		return $this->stringEncoding;
	}
	
	function getEncodedContent() {
		// UNIMPLEMENTED IN THIS BASE CLASS!!!
	}
	
	function setTheName( $strName ) {
		$this->stringName = $strName;
	}
	
	function setMimeContent( $MimeContent_content ) {
		$this->MimeContent_theContent = $MimeContent_content;
	}
	
	function setEncoding( $strEncoding ) {
		$this->stringEncoding = $strEncoding;
	}
	
	function toString() {
		$myString  = "Content-Type: ".$this->MimeContent_theContent->getMimeType()."; name=\"".$this->MimeContent_theContent->getName()."\"\n";
		
		$myString .= "Content-Disposition: attachment; filename=\"".$this->MimeContent_theContent->getName()."\"\n";
		$myString .= "Content-Transfer-Encoding: ".$this->stringEncoding."\n\n";
		$myString .= $this->getEncodedContent()."\n\n"; 
		
		return $myString;
	}
}




class MimeContent {
	
	var $stringName;
	var $stringContent;
	var $stringMimeType;
	
	function MimeContent( $strName, $strContent, $strMimeType ) {
		$this->stringName = $strName;
		$this->stringContent = $strContent;
		$this->stringMimeType = $strMimeType;
	}
	
	function getName() {
		return $this->stringName;
	}
	
	function getContent() {
		return $this->stringContent;
	}
	
	function getMimeType() {
		return $this->stringMimeType;
	}
	
	function setTheName( $strName ) {
		$this->stringName = $strName;
	}
	
	function setContent( $strContent ) {
		$this->stringContent = $strContent;
	}
	
	function setMimeType() {
		$this->stringMimeType = $strMimeType;
	}
}




class MimeFile extends MimeContent {
	
	function MimeFile( $strName, $strContentFilename, $strMimeType ) {
		$this->stringName = $strName;
		$this->setContent( $strContentFilename );
		$this->stringMimeType = $strMimeType;
	}
	
	function setContent( $strContentFilename ) {
		$handle = fopen( $strContentFilename, 'rb' );
		if( $handle ) {
			$content = '';
			while( !feof( $handle ) ) $content .= fread( $handle, 8192 );
			fclose( $handle );
			$this->stringContent = $content;
		}
		else return FALSE;
	}
}




class Base64MimeAttachment extends AbstractMimeAttachment {
	
	function Base64MimeAttachment( $strName, $MimeContent_content ) {
		parent::setAll( $strName, $MimeContent_content, 'base64' );
	}
	
	function setEncoding() {
		return FALSE;	// you may not set the encoding
	}
	
	function getEncodedContent() {
		//echo '<br><br>'.htmlspecialchars( $this->MimeContent_theContent->getContent() ).'<br>';
		$return = chunk_split( base64_encode( $this->MimeContent_theContent->getContent() ) );
		return $return;
	}
}




class WoosterLDAP {
	
	var $stringHost;
	
	function WoosterLDAP( $strHost = "ldap.wooster.edu" ) {
		$this->stringHost = $strHost;
	}
		
	function verifyUsername( $strUsername ) {
		$link = ldap_connect( $this->stringHost );
		ldap_bind( $link, 'cn=LDAP_Reader,ou=net_telcom,ou=staff,o=wooster', 'supersecretreader' );
		$result = ldap_search( $link, "o=Wooster", "cn=$strUsername" );
		$return = ( ldap_count_entries( $link, $result ) );
		ldap_close( $link );
		return $return;
	}
}

/*
if( $_POST[ 'Submit' ] ) {
	
	echo 'Something should happen here.<br><br>';
	
	$mail = new CampusMimeMail();
	$mail->addRecipient( 'jwietelmann@wooster.edu' );
	$mail->addRecipient( 'jwietlemann@wooster.edu' );
	$mail->addRecipient( 'thompsonm@wooster.edu' );
	$mail->addRecipient( 'thomsponm@wooster.edu' );
	$mail->addRecipient( 'mbenchoff@wooster.edu' );
	$mail->addRecipient( 'mbechoff@wooster.edu' );
	$mail->addRecipient( 'blah' );
	$mail->removeRecipient( 'blah' );
	$mail->setFromAddress( 'jwietelmann@wooster.edu' );
	$mail->setFromName( 'Joel Wietelmann' );
	$mail->setReplyAddress( 'jwietelmann@wooster.edu' );
	$mail->setSubject( 'Testing CampusMimeMail w/attachment' );
	$mail->setMessage( 'This is a test of the CampusMimeMail class WITH use of attachments.' );
	
	$mail->addAttachment( new MimeFile( $_FILES[ 'AttachmentFile' ][ 'tmp_name' ], $_FILES[ 'AttachmentFile' ][ 'name' ], 'text/plain' ) );
	$mail->addAttachment( new MimeFile( $_FILES[ 'AttachmentFile' ][ 'tmp_name' ], 'TEST.txt', 'text/plain' ) );
	
	$failed = $mail->sendMail();
	
	if( $failed ) echo 'Mail send succeeded.<br>';
	else echo 'Mail send failed.<br>';
	
	print_r( $failed[ 'To' ] ); echo '<br>';
	//print_r( $failed[ 'Cc' ] ); echo '<br>';
	//print_r( $failed[ 'Bcc' ] ); echo '<br>';
	echo 'This is the end.<br><br>';
}

else {
	echo '
		<form enctype="multipart/form-data" action="'.basename($_SERVER['PHP_SELF']).'" method="post">
			<b>Specify the file to attach:</b><br><br>
			<input type="file" name="AttachmentFile"><br><br>
			<input type="Submit" value="Submit" name="Submit">
		</form>
	';
}*/

?>
