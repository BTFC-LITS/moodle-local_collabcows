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

//Databases///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$databases = "";

try
{
    $databases = "<databases>\n";	

    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":
            $databaseQuery = sprintf("SELECT D.id, D.course, D.name, D.intro, D.approval, D.timeavailablefrom, D.timeavailableto, CM.id as cmid". 
                                     " FROM " . $moodleTablePrefix . "data D, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M".
                                     " WHERE D.course IN (%s)".
                                     " AND D.timeavailablefrom < %s".
                                     " AND M.name = '%s'".
                                     " AND M.visible = '1'".
                                     " AND CM.visible = '1'".
                                     " AND CM.instance = D.id".
                                     " AND CM.module = M.id",									  
                                      implode(",",$courseIDArray),
                                      time(),	
                                      "data"								  
                                    );
            break;
        default:
            throw new exception ("There is no Database query for this version of Moodle. Please contact support");
            break;
    }
    
    $debugData["query_Database"] = makeSafeForOutput($databaseQuery);
    
    $databaseResult = mysql_query($databaseQuery, $connection);
    
    if ($databaseResult)
    {
        while ($databaseRow = mysql_fetch_assoc($databaseResult)) 
        {	
            $databases .= "<database>\n";
            
            foreach ($databaseRow as $key => $value)
            {		
                $databases .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
            }
            
            $url = "";
            
            switch($moodleVersion)
            {
                case "2.0":
                case "2.1":
                case "2.2":
                case "2.3":
                case "2.4":
                case "2.5":
                    $url = "mod/data/view.php?id=" . $databaseRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Database URL Stub this version of Moodle. Please contact support");
                    break;
            }
            
            $databases .= "<url>".$url."</url>\n";
            
            if (in_array("DAX", $getData, false) || $getAllData === true)
            {
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":	
                        $databaseSubmissionsQuery = sprintf("SELECT id, timecreated, timemodified, approved". 
                                                             " FROM " . $moodleTablePrefix . "data_records". 
                                                             " WHERE dataid = '%s'". 
                                                             " AND userid = '%s'",
                                                             $databaseRow['id'], 
                                                             $userID); 
                        break;
                    default:
                        throw new exception ("There is no Database Submissions query for this version of Moodle. Please contact support");
                        break;
                }

                $debugData["query_DatabaseSubmissions"] = makeSafeForOutput($databaseSubmissionsQuery);							
                
                $databaseSubmissionsResult = mysql_query($databaseSubmissionsQuery, $connection);
                
                $numDatabaseSubmissions = 0;
                $numApprovedDatabaseSubmissions = 0;							
                $databaseSubmissions = "<submissions>\n";
                
                if ($databaseSubmissionsResult)
                {										
                    while ($databaseSubmissionsRow = mysql_fetch_assoc($databaseSubmissionsResult)) 
                    {	
                        $numDatabaseSubmissions++;
                        $databaseSubmissions .= "<submission>\n";
                        
                        foreach ($databaseSubmissionsRow as $key => $value)
                        {		
                            $databaseSubmissions .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
                            
                            if ($key == "approved" && $value == "1")
                            {
                                $numApprovedDatabaseSubmissions++;
                            }
                        }
                        
                        $databaseSubmissions .= "</submission>\n";
                    }						
                }
                else
                {
                    $numDatabaseSubmissions = -1;
                    $numApprovedDatabaseSubmissions = -1;								
                }
                
                $databaseSubmissions .= "</submissions>\n";
                
                $databases .= "<numsubmissions>" . $numDatabaseSubmissions . "</numsubmissions>\n";
                $databases .= "<numsubmissionsapproved>" . $numApprovedDatabaseSubmissions . "</numsubmissionsapproved>\n";
                
                $databases .= $databaseSubmissions;
            }
            else
            {
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":	
                        $databaseSubmissionsQuery = sprintf("SELECT COUNT(*) AS num". 
                                                             " FROM " . $moodleTablePrefix . "data_records". 
                                                             " WHERE dataid = '%s'". 
                                                             " AND userid = '%s'",
                                                             $databaseRow['id'], 
                                                             $userID
                                                             ); 
                        $databaseApprovedQuery = sprintf("SELECT COUNT(*) AS num". 
                                                          " FROM " . $moodleTablePrefix . "data_records". 
                                                          " WHERE approved = '1'".
                                                          " AND dataid = '%s'". 
                                                          " AND userid = '%s'",
                                                          $databaseRow['id'], 
                                                          $userID
                                                          );
                        break;
                    default:
                        throw new exception ("There is no Database Submissions Count query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_DatabaseSubmissions"] = makeSafeForOutput($databaseSubmissionsQuery);	
                
                $subs = mysql_fetch_assoc(mysql_query($databaseSubmissionsQuery));
                $approved = mysql_fetch_assoc(mysql_query($databaseApprovedQuery));
                
                $databases .= "<numsubmissions>" . $subs['num'] . "</numsubmissions>\n";
                $databases .= "<numsubmissionsapproved>" . $approved['num'] . "</numsubmissionsapproved>\n";
            }
            
            $databases .= "</database>\n";
        }
    }
    
    $databases .= "</databases>\n";
    
}
catch (Exception $ex)
{
    $databases = "<databases>\n";
    $databases .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $databases .= "</databases>\n";
}

$output .= $databases;

?>