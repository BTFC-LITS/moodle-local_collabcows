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

	define("COLLABCO_FUNCTIONS", "1.0.0");

	function buildErrorMessage($errorToDisplay)
	{
		$item = "An unexpected error occured whilst processing this request";

	    $item = $errorToDisplay;
		
		return "<error>" . makeSafeForOutput($item) . "</error>\n";			
	}
	
	function makeSafeForMYSQL($str) 
	{		
		$str = trim($str);
		$str = mysql_real_escape_string($str);		
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		return $str;
	}
	
	function makeSafeForOutput($str)
	{
		$str = trim($str);
		$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');		
		$str = preg_replace('/<[^>]*>/', '', $str);
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		return $str;
	}	
?>