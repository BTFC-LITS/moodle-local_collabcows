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

try 
{
    @date_default_timezone_set('UTC');   
    
    require_once('../../config.php');
    
    global $CFG;

    $username = '';
    $signature = '';
    $getData = '';
    $timestamp = '';

    if (isset($_SERVER['HTTP_HUB_TIMESTAMP']))
    {
        $timestamp = $_SERVER['HTTP_HUB_TIMESTAMP'];
        $debugData["var_timestamp"] = $timestamp;
    }
    else
    {
        throw new exception("Timestamp parameter missing or invalid ");
    }

    if (isset($_REQUEST['sig']) && strlen($_REQUEST['sig']) == 32)
    {
        $signature = $_REQUEST['sig'];
        $debugData["var_signature"] = $signature;
    } 
    else 
    {
        throw new exception("Signature parameter missing or invalid ");
    }

    if (isset($_REQUEST['un']) && strlen($_REQUEST['un']) > 0)
    {
        $username =  $_REQUEST['un'];
        $debugData["var_username"] = $username;
    } 
    else 
    {
        throw new exception("User parameter missing");
    }

    if (isset($_REQUEST['data'])) 
    {
        $getData = $_REQUEST['data'];
        $debugData["var_data"] = $getData;
    }
    else
    {
        throw new exception("Data parameter missing");
    }

    ///// XML-RPC CALL //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $serverurl = $CFG->local_collabcows_baseURL . '/webservice/xmlrpc/server.php'. '?wstoken=' . $CFG->local_collabcows_token;
    require_once('./lib/curl.php');
    $curl = new curl;
    $post = xmlrpc_encode_request('local_collabcows_get_data', array($timestamp,$signature,$username,$getData));

    $resp = xmlrpc_decode($curl->post($serverurl, $post));
    header('Content-Type: text/xml');
    
    if (isset($resp["faultCode"]))
    {
        echo "<data><error>" . $resp["faultCode"] . " - " . $resp["faultString"] . "</error></data>";  
    }    
    else if (isset($resp["errorMsg"]))
    {
        echo "<data><error>" . $resp["errorMsg"] . "</error></data>";         
    }
    else
    {
        header("hub_verification_hash: ". $resp["encHash"]);
        print_r($resp["encData"]);  
    }
}
catch (Exception $ex)
{
    header('Content-Type: text/xml');
    echo "<data><error>" . $ex . "</error></data>";
}
?>