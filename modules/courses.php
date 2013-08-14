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

//Courses/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$courses = "";

try 
{
    $courses = "<courses>\n";
    
    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":
            $courseQuery = sprintf("SELECT DISTINCT CS.id, CS.category, CS.shortname, CS.fullname, CS.summary".
                                    " FROM " . $moodleTablePrefix . "context C, " . $moodleTablePrefix . "role_assignments R, " . $moodleTablePrefix . "course CS".
                                    " WHERE CS.id = C.instanceid". 
                                    " AND CS.visible = '1'". 
                                    " AND C.id = R.contextid". 
                                    " AND C.contextlevel = '50'". 
                                    " AND R.userid = '%s'", 
                                   $userID
                                   );
            break;
        default:
            throw new exception ("There is no Course query for this version of Moodle. Please contact support");
            break;
    }
    
    $debugData["query_Course"] = makeSafeForOutput($courseQuery);
    
    $courseResult = mysql_query($courseQuery, $connection);
    
    if (!$courseResult) 
    {	
        throw new Exception ("Could not get courses: " . mysql_error());		
    }
    
    while ($courseRow = mysql_fetch_array($courseResult, MYSQL_ASSOC)) 
    {	
        $courses .= "<course>\n";		
        
        array_push($courseIDArray, $courseRow['id']);
        
        switch($moodleVersion)
        {
            case "2.0":
            case "2.1":
            case "2.2":
            case "2.3":
            case "2.4":
            case "2.5":
                $teacherQuery = sprintf("SELECT U.id, U.username, U.firstname, U.lastname".
                                         " FROM " . $moodleTablePrefix . "role_assignments RA, " . $moodleTablePrefix . "context C, " . $moodleTablePrefix . "user U, " . $moodleTablePrefix . "role R".
                                         " WHERE C.contextlevel = '%s'".
                                         " AND C.instanceid = '%s'". 
                                         " AND R.shortname IN (%s)". 
                                         " AND RA.roleid = R.id". 
                                         " AND RA.contextid = C.id". 
                                         " AND U.id = RA.userid", 
                                         50,
                                         $courseRow['id'],
                                         "'coursecreator','teacher','noneditingteacher','editingteacher'"
                                        );
                break;
            default:
                throw new exception ("There is no Teacher query for this version of Moodle. Please contact support");
                break;
        }
        
        $debugData["query_Teacher"] = makeSafeForOutput($teacherQuery);
        
        $teacherResult = mysql_query($teacherQuery, $connection);
        
        foreach ($courseRow as $key => $value)
        {		
            $courses .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
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
                $url = "course/view.php?id=" . $courseRow['id'];
                break;
            default:
                throw new exception ("There is no Course URL Stub this version of Moodle. Please contact support");
                break;
        }
        
        $courses .= "<url>".$url."</url>\n";

        if ($teacherResult) 
        {
            $teacherArray = array();
            
            while ($teacherRow = mysql_fetch_array($teacherResult, MYSQL_ASSOC)) 
            {
                $teacherName = trim($teacherRow['firstname']) . " " . trim($teacherRow['lastname']);
                $teacherUsername = trim($teacherRow['username']);
                
                if ($teacherName != " ")
                {
                    array_push($teacherArray, trim($teacherRow['id']) . "|" . $teacherName);
                }
                else
                {
                    array_push($teacherArray, trim($teacherRow['id']) . "|" . $teacherUsername);
                }							
            }
            
            $courses .= "<teacher>".implode(",",$teacherArray)."</teacher>\n";
        }
        
        $courses .= "</course>\n";			
    }
    
    $courses .= "</courses>\n";
}
catch (Exception $ex)
{
    $courses = "<courses>\n";
    $courses .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $courses .= "</courses>\n";
}		

$output .= $courses;

?>