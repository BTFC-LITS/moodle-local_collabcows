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

//Quizzes/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$quizzes = "";

try
{
    $quizzes .= "<quizzes>\n";	

    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":		
            $quizQuery = sprintf("SELECT Q.id, Q.course, Q.name, Q.intro, Q.timeopen, Q.timeclose, CM.id as cmid".
                                  " FROM " . $moodleTablePrefix . "quiz Q, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M". 
                                  " WHERE Q.course IN (%s)". 
                                  " AND M.name = '%s'". 
                                  " AND M.visible = '1'". 
                                  " AND CM.visible = '1'".									  
                                  " AND CM.instance = Q.id".
                                  " AND CM.module = M.id",								   									  
                                  implode(",",$courseIDArray),
                                  "quiz"
                                  );
            break;
        default:
            throw new exception ("There is no Quiz query for this version of Moodle. Please contact support");
            break;
    }
    
    $debugData["query_Quiz"] = makeSafeForOutput($quizQuery);
    
    $quizResult = mysql_query($quizQuery, $connection);
    
    if ($quizResult)
    {
        while ($quizRow = mysql_fetch_assoc($quizResult)) 
        {		
            $quizzes .= "<quiz>\n";
            
            foreach ($quizRow as $key => $value)
            {		
                $quizzes .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
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
                    $url = "mod/quiz/view.php?id=" . $quizRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Quiz URL Stub this version of Moodle. Please contact support");
                    break;
            }
            
            //if($singleSignOnURLs)
            //{
            //	$quizzes .= "<url>".generateSSO($url, $username)."</url>\n";
            //}
            //else
            //{
            $quizzes .= "<url>".$url."</url>\n";
            //}
            
            //if (in_array("QUX", $getData, false) || $getAllData === true)
            //{
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":						
                        $quizSubmissionsQuery = sprintf("SELECT id, attempt, timestart, timefinish, timemodified". 
                                                         " FROM " . $moodleTablePrefix . "quiz_attempts". 
                                                         " WHERE quiz = '%s'". 
                                                         " AND userid = '%s'",
                                                         $quizRow['id'], 
                                                         $userID
                                                         );
                        break;
                    default:
                        throw new exception ("There is no Quiz Submissions query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_QuizSubmissions"] = makeSafeForOutput($quizSubmissionsQuery);

                $quizSubmissionsResult = mysql_query($quizSubmissionsQuery, $connection);
                
                $numQuizSubmissions = 0;
                $quizSubmissions = "<submissions>\n";
                
                if ($quizSubmissionsResult)
                {
                    while ($quizSubmissionRow = mysql_fetch_assoc($quizSubmissionsResult)) 
                    {	
                        $numQuizSubmissions++;
                        $quizSubmissions .= "<submission>\n";
                        
                        foreach ($quizSubmissionRow as $key => $value)
                        {		
                            $quizSubmissions .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
                        }
                        
                        $quizSubmissions .= "</submission>\n";
                    }						
                }
                else
                {
                    $numQuizSubmissions = -1;							
                }
                
                $quizSubmissions .= "</submissions>\n";
                
                $quizzes .= "<numsubmissions>" . $numQuizSubmissions . "</numsubmissions>\n";
                
                $quizzes .= $quizSubmissions;
            //}
            //else
            //{
            //    switch($moodleVersion)
            //    {
            //        case "2.0":
            //        case "2.1":
            //        case "2.2":
            //        case "2.3":
            //        case "2.4":
            //        case "2.5":						
            //            $quizSubmissionsQuery = sprintf("SELECT COUNT (*) AS num".
            //                                             " FROM " . $moodleTablePrefix . "quiz_attempts". 
            //                                             " WHERE quiz = '%s'". 
            //                                             " AND userid = '%s'",
            //                                             $quizRow['id'], 
            //                                             $userID
            //                                             );
            //            break;
            //        default:
            //            throw new exception ("There is no Quiz Submissions Count query for this version of Moodle. Please contact support");
            //            break;
            //    }
                
            //    $debugData["query_QuizSubmissions"] = makeSafeForOutput($quizSubmissionsQuery);
                
            //    $subs = mysql_query($quizSubmissionsQuery);
            //    $row = mysql_fetch_assoc($subs);
                
            //    $quizzes .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";							
            //}
            
            $quizzes .= "</quiz>\n";
        }
    }
    $quizzes .= "</quizzes>\n";
}
catch (Exception $ex)
{
    $quizzes = "<quizzes>\n";
    $quizzes .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $quizzes .= "</quizzes>\n";
}

$output .= $quizzes;

?>