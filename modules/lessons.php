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

//Lessons/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$lessons = "";

try
{
    $lessons = "<lessons>\n";	
    
    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":	
            $lessonQuery = sprintf("SELECT L.id, L.course, L.name, L.available, L.deadline, CM.id as cmid". 
                                    " FROM " . $moodleTablePrefix . "lesson L, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M". 
                                    " WHERE L.course IN (%s)".  
                                    " AND L.available < %s".
                                    " AND M.name = '%s'". 
                                    " AND CM.visible = '1'". 
                                    " AND M.visible = '1'". 
                                    " AND CM.instance = L.id".
                                    " AND CM.module = M.id", 
                                    implode(",",$courseIDArray),
                                    time(),
                                    "lesson"
                                    );
            break;
        default:
            throw new exception ("There is no Lesson query for this version of Moodle. Please contact support");
            break;
    }	

    $debugData["query_Lesson"] = makeSafeForOutput($lessonQuery);
    
    $lessonResult = mysql_query($lessonQuery, $connection);		
    
    if ($lessonResult)
    {
        while ($lessonRow = mysql_fetch_assoc($lessonResult)) 
        {	
            $lessons .= "<lesson>\n";
            
            foreach ($lessonRow as $key => $value)
            {		
                $lessons .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
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
                    $url = "mod/lesson/view.php?id=" . $lessonRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Lesson URL Stub this version of Moodle. Please contact support");
                    break;
            }
            
            //if($singleSignOnURLs)
            //{
            //	$lessons .= "<url>".generateSSO($url, $username)."</url>\n";
            //}
            //else
            //{
            $lessons .= "<url>".$url."</url>\n";
            //}
            
            if (in_array("LEX", $getData, false) || $getAllData === true)
            {						
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":
                        $lessonGradesQuery = sprintf("SELECT id, grade, late, completed". 
                                                      " FROM " . $moodleTablePrefix . "lesson_grades". 
                                                      " WHERE lessonid = '%s'". 
                                                      " AND userid = '%s'",
                                                      $lessonRow['id'], 
                                                      $userID
                                                      );
                        break;
                    default:
                        throw new exception ("There is no Lesson Submission query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_LessonGrades"] = makeSafeForOutput($lessonGradesQuery);
                
                $lessonGradesResult = mysql_query($lessonGradesQuery, $connection);
                
                $numLessonGrades = 0;
                $lessonGrades = "<grades>\n";
                
                if ($lessonGradesResult)
                {
                    while ($lessonGradesRow = mysql_fetch_assoc($lessonGradesResult)) 
                    {	
                        $numLessonGrades++;
                        $lessonGrades .= "<grade>\n";
                        
                        foreach ($lessonGradesRow as $key => $value)
                        {		
                            $lessonGrades .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
                        }
                        
                        $lessonGrades .= "</grade>\n";
                    }						
                }
                else
                {
                    $numLessonGrades = -1;							
                }
                
                $lessonGrades .= "</grades>\n";
                
                $lessons .= "<numsubmissions>" . $numLessonGrades . "</numsubmissions>\n";
                
                
                $lessons .= $lessonGrades;
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
                        $lessonGradesQuery = sprintf("SELECT COUNT(*) AS num". 
                                                      " FROM " . $moodleTablePrefix . "lesson_grades". 
                                                      " WHERE lessonid = '%s'". 
                                                      " AND userid = '%s'",
                                                      $lessonRow['id'], 
                                                      $userID
                                                      );
                        break;
                    default:
                        throw new exception ("There is no Lesson Submission Count query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_LessonGrades"] = makeSafeForOutput($lessonGradesQuery);
                
                $subs = mysql_query($lessonGradesQuery);
                $row = mysql_fetch_assoc($subs);

                $lessons .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";							
            }
            
            $lessons .= "</lesson>\n";
        }
    }
    $lessons .= "</lessons>\n";
}
catch (Exception $ex)
{
    $lessons = "<lessons>\n";
    $lessons .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $lessons .= "</lessons>\n";
}

$output .= $lessons;

?>