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

//Workshops///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$workshops = "";

try
{	
    
    $workshops = "<workshops>\n";
    
    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":
            $workshopQuery = sprintf("SELECT W.id, W.course, W.name, W.intro as description, W.submissionstart, W.submissionend, CM.id as cmid".
                                      " FROM " . $moodleTablePrefix . "workshop W, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M". 
                                      " WHERE W.course IN (%s)". 
                                      " AND M.name = '%s'". 
                                      " AND CM.visible = '1'". 
                                      " AND M.visible = '1'". 
                                      " AND CM.instance = W.id".
                                      " AND CM.module = M.id", 
                                      implode(",",$courseIDArray),
                                      "workshop"
                                    );
            break;
        default:
            throw new exception ("There is no Workshop query for this version of Moodle. Please contact support");
            break;
    }
    
    $debugData["query_Workshop"] = makeSafeForOutput($workshopQuery);
    
    $workshopResult = mysql_query($workshopQuery, $connection);		
    
    
    if ($workshopResult)
    {
        while ($workshopRow = mysql_fetch_assoc($workshopResult)) 
        {
            $workshops .= "<workshop>\n";
            
            foreach ($workshopRow as $key => $value)
            {		
                $workshops .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
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
                    $url = "mod/workshop/view.php?id=" . $workshopRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Workshop URL Stub this version of Moodle. Please contact support");
                    break;
            }

            //if($singleSignOnURLs)
            //{
            //	$workshops .= "<url>".generateSSO($url, $username)."</url>\n";
            //}
            //else
            //{
            $workshops .= "<url>".$url."</url>\n";
            //}
            
            //if (in_array("WOX", $getData, false) || $getAllData === true)
            //{						
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":	
                        $workshopSubmissionsQuery = sprintf("SELECT id, timecreated, grade as finalgrade, late". 
                                                             " FROM " . $moodleTablePrefix . "workshop_submissions". 
                                                             " WHERE workshopid = '%s'". 
                                                             " AND authorid = '%s'",
                                                             $workshopRow['id'], 
                                                             $userID
                                                             );
                        break;
                    
                    default:
                        throw new exception ("There is no Workshop Submissions query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_WorkshopSubmissions"] = makeSafeForOutput($workshopSubmissionsQuery);

                $workshopSubmissionsResult = mysql_query($workshopSubmissionsQuery, $connection);
                
                $numWorkshopSubmissions = 0;
                $workshopSubmissions = "<submissions>\n";
                
                if ($workshopSubmissionsResult)
                {
                    while ($workshopSubmissionRow = mysql_fetch_assoc($workshopSubmissionsResult)) 
                    {	
                        $numWorkshopSubmissions++;
                        $workshopSubmissions .= "<submission>\n";
                        
                        foreach ($workshopSubmissionRow as $key => $value)
                        {		
                            $workshopSubmissions .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
                        }
                        
                        $workshopSubmissions .= "</submission>\n";
                    }						
                }
                else
                {
                    $numWorkshopSubmissions = -1;							
                }
                
                $workshopSubmissions .= "</submissions>\n";
                
                $workshops .= "<numsubmissions>" . $numWorkshopSubmissions . "</numsubmissions>\n";
                
                $workshops .= $workshopSubmissions;
            //}
            //else
            //{
            //    switch($moodleVersion)
            //    {
            //        case "1.9":								
            //            $workshopSubmissionsQuery = sprintf("SELECT COUNT(*) AS num". 
            //                                                 " FROM " . $moodleTablePrefix . "workshop_submissions". 
            //                                                 " WHERE workshopid = '%s'". 
            //                                                 " AND userid = '%s'",
            //                                                 $workshopRow['id'], 
            //                                                 $userID
            //                                                 );
            //            break;
            //        case "2.3":
            //        case "2.1":
            //        case "2.2":
            //            $workshopSubmissionsQuery = sprintf("SELECT COUNT(*) AS num". 
            //                                                 " FROM " . $moodleTablePrefix . "workshop_submissions". 
            //                                                 " WHERE workshopid = '%s'". 
            //                                                 " AND authorid = '%s'",
            //                                                 $workshopRow['id'], 
            //                                                 $userID);
            //            break;
                    
            //        default:
            //            throw new exception ("There is no Workshop Submissions query for this version of Moodle. Please contact support");
            //            break;
            //    }

            //    $debugData["query_WorkshopSubmissions"] = makeSafeForOutput($workshopSubmissionsQuery);							

            //    $subs = mysql_query($workshopSubmissionsQuery);
            //    $row = mysql_fetch_assoc($subs);
                
            //    $workshops .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";						
            //}
            
            $workshops .= "</workshop>\n";
        }
    }
    $workshops .= "</workshops>\n";
}
catch (Exception $ex)
{
    $workshops = "<workshops>\n";
    $workshops .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $workshops .= "</workshops>\n";
}

$output .= $workshops;

?>