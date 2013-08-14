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

//Assignments/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$assignments = "";

try 
{
    $assignments = "<assignments>\n";

    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
            $assignmentQuery = sprintf("SELECT DISTINCT A.id, A.name, A.course, A.intro as description, A.timedue, A.timeavailable, A.timemodified, CM.id as cmid".
                                        " FROM " . $moodleTablePrefix . "assignment A, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M".
                                        " WHERE A.timeavailable <= %s". 
                                        " AND A.course IN (%s)". 
                                        " AND M.name = '%s'". 
                                        " AND CM.instance = A.id". 
                                        " AND CM.module = M.id". 
                                        " AND CM.visible = '1'". 
                                        " AND M.visible = '1'", 
                                        time(), 
                                        implode(",",$courseIDArray),
                                        "assignment"											
                                        );
            break;					
        case "2.3":
        case "2.4":
        case "2.5":						
            $assignmentQuery = sprintf("SELECT DISTINCT A.id, A.name, A.course, A.intro AS description, A.allowsubmissionsfromdate as timeavailable, A.duedate as timedue, A.timemodified, CM.id AS cmid".
                                        " FROM " . $moodleTablePrefix . "assign A, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M".
                                        " WHERE A.allowsubmissionsfromdate <= %s". 
                                        " AND A.course IN (%s)". 
                                        " AND M.name = '%s'". 
                                        " AND CM.instance = A.id". 
                                        " AND CM.module = M.id". 
                                        " AND CM.visible = '1'". 
                                        " AND M.visible = '1'", 
                                        time(), 
                                        implode(",",$courseIDArray),
                                        "assign"											
                                        );												
            break;
        default:
            throw new exception ("There is no Assignment query for this version of Moodle. Please contact support");
            break;
    }
    

    
    $debugData["query_Assignment"] = makeSafeForOutput($assignmentQuery);		
    
    $assignmentResult = mysql_query($assignmentQuery, $connection);
    
    if ($assignmentResult)
    {
        while ($assignmentRow = mysql_fetch_assoc($assignmentResult)) 
        {
            $assignments .= "<assignment>\n";
            
            foreach ($assignmentRow as $key => $value)
            {		
                $assignments .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
            }
            
            $url = "";
            
            switch($moodleVersion)
            {
                case "2.0":
                case "2.1":
                case "2.2":	
                    $url = "mod/assignment/view.php?id=" . $assignmentRow['cmid'];
                    break;
                case "2.3":
                case "2.4":
                case "2.5":
                    $url = "mod/assign/view.php?id=" . $assignmentRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Assignemt URL Stub for this version of Moodle. Please contact support");
                    break;
            }

            $assignments .= "<url>".$url."</url>\n";				

            $assignmentSubmissions = "<submissions>\n";
            
            if (in_array("ASX", $getData, false) || $getAllData === true)
            {

                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":                            
                        $submissionQuery = sprintf("SELECT id, timemodified, timecreated, status, attemptnumber". 
                                                " FROM " . $moodleTablePrefix . "assign_submission". 
                                                " WHERE assignment = '%s'". 
                                                " AND userid = '%s'", 
                                                $assignmentRow['id'], 
                                                $userID
                                                );
                        break;
                    default:
                        throw new exception ("There is no Assignment Submission query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_AssignmentSubmission_" . $assignmentRow['id']] = makeSafeForOutput($submissionQuery);	
                
                $submissionResult = mysql_query($submissionQuery , $connection);
                
                $numAssSubmissions = 0;
                
                if ($submissionResult)
                {								
                    while ($submissionRow = mysql_fetch_assoc($submissionResult)) 
                    {
                        $numAssSubmissions++;
                        
                        $assignmentSubmissions .= "<submission>\n";
                        
                        foreach ($submissionRow as $key => $value)
                        {		
                            $assignmentSubmissions .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
                        }
                        
                        $assignmentSubmissions .= "</submission>\n";
                    }
                }                
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
                        $submissionQuery = sprintf("SELECT COUNT(*) AS num". 
                                                    " FROM " . $moodleTablePrefix . "assignment_submissions". 
                                                    " WHERE assignment = '%s'". 
                                                    " AND userid = '%s'", 
                                                    $assignmentRow['id'], 
                                                    $userID
                                                    );
                        break;
                    default:
                        throw new exception ("There is no Assignment Submission Count query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_AssignmentSubmissions"] = makeSafeForOutput($submissionQuery);	
                
                $subs = mysql_query($submissionQuery);
                $row = mysql_fetch_assoc($subs);

                $assignments .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";
            }
            
            $assignmentSubmissions .= "</submissions>\n";
            
            $assignments .= "<numsubmissions>" . $numAssSubmissions . "</numsubmissions>\n";				

            $assignments .= $assignmentSubmissions;
            
            $assignments .= "</assignment>\n";

        }
    }
    
    $assignments .= "</assignments>\n";
}
catch (Exception $ex)
{
    $assignments = "<assignments>\n";
    $assignments .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $assignments .= "</assignments>\n";
}

$output .= $assignments;

?>