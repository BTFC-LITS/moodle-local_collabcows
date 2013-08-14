<?php
/*****************************************************************************************************************************************/
/*    ____ ___  _     _        _    ____   ____ ___  
/*   / ___/ _ \| |   | |      / \  | __ ) / ___/ _ \ 
/*  | |  | | | | |   | |     / _ \ |  _ \| |  | | | |
/*  | |__| |_| | |___| |___ / ___ \| |_) | |__| |_| |
/*   \____\___/|_____|_____/_/   \_\____/ \____\___/ 
/* 
/*****************************************************************************************************************************************/
/*  Author:			Collabco Software (Oli Newsham)
/*  Support:		support@collabco.co.uk
/*  Website:		Collabco.co.uk
/*  Twitter:		@collabco
/*****************************************************************************************************************************************/
/*
/*  This source code must retain the above copyright notice, this list of conditions and the following disclaimer.
/*
/*  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT 
/*  NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL 
/*  THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
/*  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
/*  HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
/*  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
/*
/*****************************************************************************************************************************************/
	
	if (!defined("COLLABCO_MOODLE"))
	{
		die();
	}
	
	define("COLLABCO_CRYPTO", "1.0.0");

	function generateKey($username,$serverSalt) {		
		return hash('sha256', mb_convert_encoding($serverSalt . strtoupper($username), "UTF-16LE"), true );
	}  

	function encrypt($plaintext, $username, $serverSalt) {
		srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
		 
		if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22)
		{	 
			return false;
		}
	
		
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, generateKey($username,$serverSalt), $plaintext, MCRYPT_MODE_CBC, $iv));
        
        $verificationhash = md5(trim($plaintext));

		return $verificationhash . " " . $iv_base64 . $encrypted;
	 }
?>
