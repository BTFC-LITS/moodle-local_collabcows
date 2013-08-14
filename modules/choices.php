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

//Choices/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$choices = "";

try
{				
    switch($moodleVersion)
    {
        case "2.0":
        case "2.1":
        case "2.2":
        case "2.3":
        case "2.4":
        case "2.5":				
            $choiceQuery = sprintf("SELECT C.id, C.course, C.name, C.intro as text, C.timeopen, C.timeclose, CM.id as cmid". 
                                    " FROM " . $moodleTablePrefix . "choice C, " . $moodleTablePrefix . "course_modules CM, " . $moodleTablePrefix . "modules M". 
                                    " WHERE C.course IN (%s)". 
                                    " AND M.name = '%s'".
                                    " AND M.visible = '1'".
                                    " AND CM.visible = '1'". 
                                    " AND CM.instance = C.id".
                                    " AND CM.module = M.id", 
                                    implode(",",$courseIDArray),
                                    "choice"
                                    );
            break;
        default:
            throw new exception ("There is no Choice query for this version of Moodle. Please contact support");
            break;
    }
    
    $debugData["query_Choice"] = makeSafeForOutput($choiceQuery);

    $choiceResult = mysql_query($choiceQuery, $connection);
    
    $choices = "<choices>\n";
    
    if ($choiceResult)
    {		
        while ($choiceRow = mysql_fetch_assoc($choiceResult)) 
        {
            $choices .= "<choice>\n";
            
            foreach ($choiceRow as $key => $value)
            {		
                $choices .= "<".$key.">".makeSafeForOutput($value)."</".$key.">\n";
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
                    $url = "mod/choice/view.php?id=" . $choiceRow['cmid'];
                    break;
                default:
                    throw new exception ("There is no Choice URL Stub this version of Moodle. Please contact support");
                    break;
            }

            $choices .= "<url>".$url."</url>\n";
            
            if (in_array("CHX", $getData, false) || $getAllData === true)
            {			            
                switch($moodleVersion)
                {
                    case "2.0":
                    case "2.1":
                    case "2.2":
                    case "2.3":
                    case "2.4":
                    case "2.5":						
                        $choicesSubmissionsQuery = sprintf("SELECT COUNT(*) AS num". 
                                                            " FROM " . $moodleTablePrefix . "choice_answers". 
                                                            " WHERE choiceid = '%s'". 
                                                            " AND userid = '%s'",
                                                            $choiceRow['id'], 
                                                            $userID
                                                            );
                        break;
                    default:
                        throw new exception ("There is no Choice Submissions Count query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_ChoiceSubmissions"] = makeSafeForOutput($choicesSubmissionsQuery);
                
                $subs = mysql_query($choicesSubmissionsQuery);
                $row = mysql_fetch_assoc($subs);

                $choices .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";
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
                        $choicesSubmissionsQuery = sprintf("SELECT COUNT(*) AS num". 
                                                            " FROM " . $moodleTablePrefix . "choice_answers". 
                                                            " WHERE choiceid = '%s'". 
                                                            " AND userid = '%s'",
                                                            $choiceRow['id'], 
                                                            $userID
                                                            );
                        break;
                    default:
                        throw new exception ("There is no Choice Submissions Count query for this version of Moodle. Please contact support");
                        break;
                }
                
                $debugData["query_ChoiceSubmissions"] = makeSafeForOutput($choicesSubmissionsQuery);
                
                $subs = mysql_query($choicesSubmissionsQuery);
                $row = mysql_fetch_assoc($subs);

                $choices .= "<numsubmissions>" . $row['num'] . "</numsubmissions>\n";						
            }

            $choices .= "</choice>\n";
        }
    }
    
    $choices .= "</choices>\n";
}
catch (Exception $ex)
{
    $choices = "<choices>\n";
    $choices .= buildErrorMessage("Unexpected exception: " . $ex->getMessage());
    $choices .= "</choices>\n";
}

$output .= $choices;

?>