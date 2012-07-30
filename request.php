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
$userId = $asana->getUserId();

// check if everything works fine
if($asana->getResponseCode() == '200' && $result != '' ){

    // ##############################################################################################
    // WORKSPACES
    // ##############################################################################################
    if($workspaceId == '' && $projectId == ''){
        
        $resultJson = json_decode($result);
        // $resultJson contains an object in json with all projects
        foreach($resultJson->data as $workspace){
            if($workspace->name == 'Personal Projects') continue;
            echo '<div class="span4 well workspace" data-workspace-id="' . $workspace->id .'"><h3>' . $workspace->name .'</h3></div>';
        }
    }

    // ##############################################################################################
    // TASKS
    // ##############################################################################################
    if($projectId != ''){
        
        $result = $asana->getTasks($workspaceId);
        $result = json_decode($result);

        // loop through all Tasks of the result, because the Asana-Api gives us also e.g. the completed ones
        foreach($result->data as $task){
             
             $value = $asana->getEstimatedAndWorkedTime($task->name);
             $taskState = $asana->getOneTask($task->id);

             $taskName = $value['taskName'];
             $estimatedHours = (!empty($value['estimatedHours'])) ? $value['estimatedHours'].'h' : '0h';
             $estimatedMinutes = (!empty($value['estimatedMinutes'])) ? $value['estimatedMinutes'].'m' : '0m';
             $workedHours = (!empty($value['workedHours'])) ? $value['workedHours'].'h' : '0h';
             $workedMinutes = (!empty($value['workedMinutes'])) ? $value['workedMinutes'].'m' : '0m';
             $workedTime = $value['workedTimeSec'];
             
             // progress bar
             $progressBarPercent = ($estimatedHours*60*1000 + $estimatedMinutes * 1000) / 100;
             
             if($progressBarPercent != 0){
                $progressBarPercent = ($workedHours*60*1000 + $workedMinutes * 1000) / $progressBarPercent;
             }
             
             if($progressBarPercent == '') $progressBarPercent = 100;
             
             $progressState = ($progressBarPercent < 80) ? 'progress-success' : (($progressBarPercent < 100 ) ? 'progress-warning' : 'progress-danger');
             
             // task must be active and your own
             if($taskState['completed'] || $taskState['assignee'] != $userId) {
                continue;
             } else {
                echo '<tr>'
                    .'<td>'. $taskState['projects']['name'] .'</td>'
                    .'<td>'. $taskName  .'</td>'
                    .'<td class="estimated_time" data-estimated-hours="'.$value['estimatedHours'].'" data-estimated-minutes="'.$value['estimatedMinutes'].'">'
                        . '<span class="my_label" rel="tooltip" title="click to edit">' . $estimatedHours .' '. $estimatedMinutes . '</span>'
                        . '<input class="date-picker-et" name="date-picker-et"/>'
                    . '</td>'
                    .'<td class="worked_time" data-worked-hours="'.$value['workedHours'].'" data-worked-minutes="'.$value['workedMinutes'].'" data-task-id="' . $task->id . '" data-task-name="' . $taskName . '">'
                        . '<span class="my_label" rel="tooltip" title="click to edit">' . $workedHours .' '. $workedMinutes . '</span>'
                        . '<input class="date-picker-wt" name="date-picker-wt"/>'
                    . '</td>'
                    .'<td class="my_progress"><div class="progress ' . $progressState . ' progress-striped">
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
    
    // ##############################################################################################
    // UPDATE
    // ##############################################################################################
    if($updateId != ''){
        
        $workedHours = $_GET['workedHours'];
        $workedMinutes = $_GET['workedMinutes'];
        $estimatedHours = $_GET['estimatedHours'];
        $estimatedMinutes = $_GET['estimatedMinutes'];
        $currentTaskName = $_GET['taskName'];
        
        $asana->updateTask($updateId, $workedHours, $workedMinutes, $estimatedHours, $estimatedMinutes,  $currentTaskName);
    }
    

} else {
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.</p>';
}