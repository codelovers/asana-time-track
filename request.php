<?php
/**
 * AsanaTimeTracking-Tool
 * - track your time for every single task
 *
 * @author codelovers
 * @author codelovers.de
 * @version 1.0 (2012_07_07)
 * @package asana_track_time
 */

require_once("AsanaApi.php");

// get data
$apiKey = $_GET['apiKey'];
$workspaceId = $_GET['workspaceId'];
$projectId = $_GET['projectId'];
$updateId = $_GET['updateId'];

// initalize
$asana = new AsanaApi($apiKey); 
$result = $asana->getWorkspaces();
$userId = $asana->getUserId(); // should be saved in cookie

// check if everything works fine
if($asana->getResponseCode() == '200' && $result != '' ){
	    
    // workspaces
    ////////////////////////////
    if($workspaceId == '' && $projectId == ''){
        
       //echo '<b>Workspaces:</b><br />';

    	$resultJson = json_decode($result);
    	// $resultJson contains an object in json with all projects
    	foreach($resultJson->data as $workspace){
    		//echo '- <a href="?apiKey='.$apiKey.'&workspaceId=' . $workspace->id.'" target="_self">'. $workspace->name .'</a><br>';
            if($workspace->name == 'Personal Projects') continue;
            echo '<div class="span4 well workspace" data-workspace-id="' . $workspace->id .'"><h3>' . $workspace->name .'</h3></div>';
    	}
    }

    // tasks
    ////////////////////////////
    if($projectId != ''){
          
        //echo '<b>Tasks:</b><br />';
        
        $result = $asana->getTasks($workspaceId);
        $result = json_decode($result);

        foreach($result->data as $task){
             
             $value = $asana->getGuessAndWorkedTime($task->name);
             $taskState = $asana->getOneTask($task->id);

             $taskName = $value['taskName'];
             $guessHours = (!empty($value['guessHours'])) ? $value['guessHours'].'h' : '0h';
             $guessMinutes = (!empty($value['guessMinutes'])) ? $value['guessMinutes'].'m' : '0m';
             $workedHours = (!empty($value['workedHours'])) ? $value['workedHours'].'h' : '0h';
             $workedMinutes = (!empty($value['workedMinutes'])) ? $value['workedMinutes'].'m' : '0m';
             $workedTime = $value['workedTimeSec'];
             
             // progress bar
            $progressBarPercent = ($guessHours*60*1000 + $guessMinutes * 1000) / 100;
            $progressBarPercent = ($workedHours*60*1000 + $workedMinutes * 1000) / $progressBarPercent;
             
             $progressState = ($progressBarPercent < 90) ? 'progress-success' : (($progressBarPercent < 100 ) ? 'progress-warning' : 'progress-danger');
             
             // task must be active and your own   
             if($taskState['completed'] || $taskState['assignee'] != $userId) {
                continue;
             } else {
                echo '<tr>'
                    .'<td>'. $taskState['projects']["name"] .'</td>'
                    .'<td>'. $taskName  .'</td>'
                    .'<td>'. $guessHours .' '. $guessMinutes .'</td>'
                    .'<td class="worked_time" data-guess-hours="'.$value['guessHours'].'" data-guess-minutes="'.$value['guessMinutes'].'" data-worked-hours="'.$value['workedHours'].'" data-worked-minutes="'.$value['workedMinutes'].'" data-task-id="' . $task->id . '" data-task-name="' . $taskName . '">'. $workedHours .' '. $workedMinutes .'</td>'
                    .'<td><div class="progress ' . $progressState . ' progress-striped">
                            <div class="bar" style="width: ' . $progressBarPercent . '%;"></div>
                        </div>
                      </td>'
                    .'<td class="my_timer">
                        <div class="time">00:00:00</div>
                        <button class="btn btn-success" type="submit">
                            <i class="icon-white icon-play"></i><span class="start_stop_text">Start</span>
                        </button>
                      </td>'
                    .'</tr>';
                 
             }
         }

         echo '<tr class="worked_time_line"><td colspan="4"><td class="text_align right">Worked today:</td><td class="worked_time_today">0 hours 0 minutes</td></tr>';
                 
        
    }
    
    // update
    ////////////////////////////    
    if($updateId != ''){
        
        $workedHours = $_GET['workedHours'];
        $workedMinutes = $_GET['workedMinutes'];
        $guessHours = $_GET['guessHours'];
        $guessMinutes = $_GET['guessMinutes'];
        $currentTaskName = $_GET['taskName'];
        
        $asana->updateTask($updateId, $workedHours, $workedMinutes, $guessHours, $guessMinutes,  $currentTaskName);
    }
    

} else {
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.</p>';
}