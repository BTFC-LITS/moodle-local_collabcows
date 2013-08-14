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

require_once($CFG->libdir . "/externallib.php");

class local_collabcows_external extends external_api {

    public static function get_data_parameters() {
        return new external_function_parameters(
            array(
                'timestamp' => new external_value(PARAM_RAW, 'Timetamp for the secure element of the message', VALUE_REQUIRED),
                'signature' => new external_value(PARAM_RAW, 'The signed verification for the request', VALUE_REQUIRED),
                'username' => new external_value(PARAM_RAW, 'The user to get data for', VALUE_REQUIRED),
                'getData' => new external_value(PARAM_RAW, 'The data types to return', VALUE_REQUIRED)
            )
        );
    }

    public static function get_data($timestamp, $signature, $username, $getDataString) {
        global $USER, $CFG;
        
        $retArray = array();
        
        $retArray["encData"] = null;
        $retArray["encHash"] = null;
        $retArray["errorMsg"] = null;
        
        define("COLLABCO_MOODLE", "1.0.0");
        @date_default_timezone_set('UTC');

        //Parameter validation
        $params = self::validate_parameters(self::get_data_parameters(), array('timestamp' => $timestamp, 'signature' => $signature, 'username' => $username, 'getData' => $getDataString));

        //Context validation
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        //if (!has_capability('moodle/user:viewdetails', $context)) {
        //    throw new moodle_exception('cannotviewprofile');
        //}

        //require_once('config.php');  
        
        $serverSecret = $CFG->local_collabcows_secret;
        $serverSalt = $CFG->local_collabcows_salt;
        $showMetrics = $CFG->local_collabcows_metrics;
        $returnClearText = $CFG->local_collabcows_plaintext;
        $returnCipherText = $CFG->local_collabcows_ciphertext;
        $maximumTimeDrift = $CFG->local_collabcows_timedrift;
        $moodleVersion = $CFG->local_collabcows_version;
        $moodleTablePrefix = $CFG->local_collabcows_prefix;
        $moodleBaseURL = $CFG->local_collabcows_baseURL;
        $debug = $CFG->local_collabcows_debugdata;
        
        if (strlen($serverSecret) < 20)
        {
            $retArray["errorMsg"] = "Configuration step 2 has not been completed";
            return $retArray;
        }
        
        if (strlen($serverSalt) < 20)
        {
            $retArray["errorMsg"] = "Configuration step 3 has not been completed";
            return $retArray;
        }
        
        require_once("lib/functions.php");
        require_once("lib/crypto.php");
        require_once("lib/sso.php"); 
        
        $debugData = array();
        $debugData["var_showMetrics"] = $showMetrics;
        $debugData["var_returnClearText"] = $returnClearText;
        $debugData["var_returnCipherText"] = $returnCipherText;
        $debugData["var_maximumTimeDrift"] = $maximumTimeDrift;
        $debugData["var_moodleVersionOverride"] = $moodleVersion;
        $debugData["var_moodleTablePrefixOverride"] = $moodleTablePrefix;
        $debugData["timestampRecieved"] = $timestamp;
        
        $encrypted = "";
        
        try
        { 
            //Security Validation/////////////////////////////////////////////////////////////////////////////////////////////////////////
  
            $minTimeAllowed = strtotime("-" . $maximumTimeDrift . "  seconds");
            $maxTimeAllowed = strtotime("+" . $maximumTimeDrift . "  seconds");

            if (strtotime($timestamp) < $minTimeAllowed || strtotime($timestamp) > $maxTimeAllowed)
            {
                $retArray["errorMsg"] = "Timestamp (".$timestamp.") invalid. Check time on server (".date("Y-m-d\TH:i:s\Z").") and client (".$timestamp.") are correct. " . $minTimeAllowed  . " - " .  $maxTimeAllowed . ")";
                return $retArray;
            }

            $expectedSignature = strtolower(md5($serverSecret . "==" . $username . "==" . $timestamp));
    
            if (strtolower($signature) != $expectedSignature)
            {
                $retArray["errorMsg"] = "Signature incorrect " . $signature;
                return $retArray;
            }
        
            //Start tracking execution time///////////////////////////////////////////////////////////////////////////////////////////////
            
            if ($showMetrics == true)
            {
                $mtime = microtime(); 
                $mtime = explode(" ",$mtime); 
                $mtime = $mtime[1] + $mtime[0]; 
                $starttime = $mtime;
            }

            //Setup Variables/////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $output = "";
            $getData = array();            
            $getAllData = false;
            
            //Setup Connection////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $connection = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
            
            if (!$connection)
            {
                throw new exception("Could not connect: " . mysql_error());		
            }

            $selected_db = mysql_select_db($CFG->dbname, $connection);

            if (!$selected_db)
            {
                throw new exception("Could not select database: " . mysql_error());
            }
            
            //Setup Base Moodle URL///////////////////////////////////////////////////////////////////////////////////////////////////////

            if(strlen($moodleBaseURL) == 0)
            {
                $moodleBaseURL = $CFG->wwwroot;
            }
            
            $debugData["var_moodleBaseURL"] = $moodleBaseURL;
            
            //Set Moodle Table Prefix/////////////////////////////////////////////////////////////////////////////////////////////////////

            //if ($moodleTablePrefix == "")
            //{
                $moodleTablePrefix = $CFG->prefix;
            //}
            
            $debugData["var_moodleTablePrefix"] = $moodleTablePrefix;
            
            //Get Moodle Version//////////////////////////////////////////////////////////////////////////////////////////////////////////

            if ($moodleVersion == "")
            {
                require_once("version.php");		

                $moodleBuild = "";
                
                $moodleVersionString = substr($CFG->version, 0, 8);
                $moodleBuildString = substr($CFG->version, 8);
                
                switch ($moodleVersionString)
                {
                    case "20101124":
                    case "20101225":
                    case "20110221":
                    case "20110330":
                        $moodleVersion = "2.0";
                        break;
                    case "20110701":
                        $moodleVersion = "2.1";
                        break;
                    case "20111205":
                        $moodleVersion = "2.2";
                        break;
                    case "20120625":
                    case "20120701":
                        $moodleVersion = "2.3";
                        break;
                    case "20121203":
                        $moodleVersion = "2.4";
                        break;
                    case "20130514":
                        $moodleVersion = "2.5";
                        break;
                    default:
                        $moodleVersion = "UNKNOWN";
                        break;
                        break;
                }
                
                $debugData["var_moodleVersionString"] = $moodleVersionString;
                $debugData["var_moodleBuildString"] = $moodleBuildString;
                
                if ($moodleVersion == "UNKNOWN")
                {
                    throw new exception("This version of Moodle is unsupported (" . $version . ") Please contact support.");
                }
            }
            
            $debugData["var_moodleVersion"] = $moodleVersion;
            
            //Setup Data Retrieval List///////////////////////////////////////////////////////////////////////////////////////////////////
            
            $debugData["var_getData"] = trim($getDataString);
            $getData = explode(",", trim($getDataString));                   

            //Get User ID/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            switch($moodleVersion)
            {
                case "2.0":
                case "2.1":
                case "2.2":
                case "2.3":
                case "2.4":
                case "2.5":
                    $userIDQuery = sprintf("SELECT id".
                                            " FROM " . $moodleTablePrefix . "user".
                                            " WHERE username = '%s'", 
                                            makeSafeForMYSQL($username)
                                            );
                    break;
                default:
                    throw new exception ("There is no User ID query for this version of Moodle. Please contact support");
                    break;
            }

            $debugData["query_UserID"] = makeSafeForOutput($userIDQuery);
            
            $userIDResult = mysql_query($userIDQuery, $connection);
            
            if (!$userIDResult) 
            {		
                throw new exception("Could not select user: " . $username);
            }

            $row = mysql_fetch_object($userIDResult);

            $userID = $row->id; 		

            if (!$userID) 
            {		
                throw new exception("Could not get ID for user: " . $username . " " . $userIDQuery);
            }        
            
            $debugData["var_userID"] = $userID;

            $courseIDArray = array();            
            
            //Begin Output////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $output = "<data>\n";          

            //Load data from modules//////////////////////////////////////////////////////////////////////////////////////////////////////
            
            require_once("modules/courses.php");            
            
            if (count($courseIDArray) > 0)
            {                
                foreach($getData as $index => $value)
                {                    
                    switch ($value)
                    {
                        case "AS":
                        case "ASX":
                            require_once("modules/assignments.php");
                            break;
                        case "CH":
                        case "CHX":
                            require_once("modules/choices.php");
                            break;
                        case "LE":
                        case "LEX":
                            require_once("modules/lessons.php");
                            break;
                        case "WO":
                        case "WOX":
                            require_once("modules/workshops.php");
                            break;
                        case "DA":
                        case "DAX":
                            require_once("modules/databases.php");
                            break;
                        case "QU":
                        case "QUX":
                            require_once("modules/quizzes.php");
                            break;
                        case "ALL":
                            require_once("modules/assignments.php");                            
                            require_once("modules/choices.php");                            
                            require_once("modules/lessons.php");                            
                            require_once("modules/workshops.php");
                            require_once("modules/databases.php");
                            require_once("modules/quizzes.php");                            
                            break;
                    }
                }
            }
			
            //Close Off Data Stream///////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $output .= "</data>\n";
            
            //Open Encrypted Output///////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $encrypted = "<data>\n";

            //Encrypt Data////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        
            $encryptedData = encrypt($output,$username,$serverSalt);
            
            $encryptedDataArray = explode(" ", $encryptedData);
            
            if ($returnClearText == true)
            {
                $encrypted .= "<cleartext>". $output . "</cleartext>\n";
            }
            
            if ($returnCipherText == true)
            {
                $encrypted .= "<ciphertext>". $encryptedDataArray[1] . "</ciphertext>\n";
            }

            //Add Metrics to Output///////////////////////////////////////////////////////////////////////////////////////////////////////
            
            if ($showMetrics == true)
            {
                $mtime = microtime(); 
                $mtime = explode(" ",$mtime); 
                $encrypted .= "<executiontime>".(($mtime[1] + $mtime[0]) - $starttime)."</executiontime>\n";
            }
            
            //Add Instance URL to Output//////////////////////////////////////////////////////////////////////////////////////////////////
            
            //if($singleSignOnURLs)
            //{
            //	$moodleurl .= "<instanceurl>". generateSSO("index.php", $username)."</instanceurl>\n";
            //}
            //else
            //{
            $moodleurl .= "<instanceurl>" . $CFG->wwwroot . "</instanceurl>\n";
            //}
            
            $encrypted .= $moodleurl;
            
            //Add User details to Output///////////////////////////////////////////////////////////////////////////////////////////////////           
            
            $encrypted .= "<user>" . $username . "</user>\n";
            $encrypted .= "<userid>" . $userID . "</userid>\n";
            
            //Close encrypted Output///////////////////////////////////////////////////////////////////////////////////////////////////////
            
        }
        catch (exception $ex)
        {
            $encrypted = "<data>\n";
            $encrypted .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
        }
        
        //Insert Debug data into stream///////////////////////////////////////////////////////////////////////////////////////////////

        if ($debug == true)
        {	
            $debuglines = "<debugdata>\n";
            
            try
            {
                foreach ($debugData as $key => $value) 
                {
                    $debuglines .= "<$key>$value</$key>\n";
                }
            }
            catch (Exception $ex)
            {
                $debuglines .= "<error>\n<message>An exception was encountered whilst adding debug data</message>\n<exception>" . $ex . "</exception>\n</error>\n";
            }
            
            $debuglines .= "</debugdata>\n";
            
            $encrypted .= $debuglines;
        }
        
        $encrypted .= "</data>\n";        
        
        $retArray = array();
        
        $retArray["encData"] = $encrypted;
        $retArray["encHash"] = $encryptedDataArray[0];
        $retArray["errorMsg"] = null;
        
        return $retArray;
    }

    public static function get_data_returns() {           
        return new external_single_structure(
            array(
                'encData' => new external_value(PARAM_RAW, 'Encrypted assignment data'),
                'encHash' => new external_value(PARAM_RAW, 'Verification has for encrypted data'),
                'errorMsg' => new external_value(PARAM_RAW, 'Error messages')
            )
        );
    }
}
