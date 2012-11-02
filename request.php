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
$workspaceId = !empty($_GET['workspaceId']) ? $_GET['workspaceId'] : '';
$projectId = !empty($_GET['projectId']) ? $_GET['projectId'] : '';
$updateId = !empty($_GET['updateId']) ? $_GET['updateId'] : '';

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
        $in = false;
        
        $completedTasks = array();
        $pendingTasks = array();
        
        // loop through all Tasks of the result, because the Asana-Api gives us also e.g. the completed ones
        foreach($result->data as $task) {
             
             $taskData = array();
             $taskData['value'] = $asana->getEstimatedAndWorkedTime($task->name);
             $taskData['id'] = $task->id;
             $taskData['taskState'] = $asana->getOneTask($task->id);
             $taskData['estimatedHours'] = (!empty($taskData['value']['estimatedHours'])) ? $taskData['value']['estimatedHours'].'h' : '0h';
             $taskData['estimatedMinutes'] = (!empty($taskData['value']['estimatedMinutes'])) ? $taskData['value']['estimatedMinutes'].'m' : '0m';
             $taskData['workedHours'] = (!empty($taskData['value']['workedHours'])) ? $taskData['value']['workedHours'].'h' : '0h';
             $taskData['workedMinutes'] = (!empty($taskData['value']['workedMinutes'])) ? $taskData['value']['workedMinutes'].'m' : '0m';
             $taskData['workedTime'] = $taskData['value']['workedTimeSec'];
             $taskData['progressBarPercent'] = ($taskData['estimatedHours']*60*1000 + $taskData['estimatedMinutes'] * 1000) / 100;
             
             if($taskData['progressBarPercent'] != 0){
                 $taskData['progressBarPercent'] = ($taskData['workedHours']*60*1000 + $taskData['workedMinutes'] * 1000) / $taskData['progressBarPercent'];
             }
             elseif($taskData['progressBarPercent'] === '') $taskData['progressBarPercent'] = 100;
             
             $taskData['progressState'] = ($taskData['progressBarPercent'] < 80) ? 'progress-success' : (($taskData['progressBarPercent'] < 100 ) ? 'progress-warning' : 'progress-danger');
             
             // task must be active and your own
             if($taskData['taskState']['assignee'] != $userId || $taskData['value']['taskName'] == '') continue;
             elseif(!$taskData['taskState']['completed']) $pendingTasks[] = $taskData;
             elseif($task->name != $taskData['value']['taskName']) $completedTasks[] = $taskData;
         }
         
         echo '
         <h2>Pending tasks</h2>
         <table class="att_track_table table table-bordered">
             <thead>
               <tr>
                 <th>Project</th>
                 <th>Tasks (assigned to you)</th>
                 <th>Estimated Time</th>
                 <th>Worked Time</th>
                 <th>Progress</th>
                 <th>Timer</th>
               </tr>
             </thead>
             <tbody>';
          // no pending task is found
         if(count($pendingTasks) == 0) echo '<tr><td colspan="6">Sorry, no pending tasks assigned to you were found...</td></tr>';
         else {
             foreach($pendingTasks as $task) {
                 echo '<tr>'
                     .'<td>'. $task['taskState']['projects']['name'] .'</td>'
                     .'<td>'. $task['value']['taskName']  .'</td>'
                     .'<td class="estimated_time" data-estimated-hours="'.$task['value']['estimatedHours'].'" data-estimated-minutes="'.$task['value']['estimatedMinutes'].'">'
                         . '<span class="my_label" rel="tooltip" title="click to edit">' . $task['estimatedHours'] .' '. $task['estimatedMinutes'] . '</span>'
                         . '<input class="date-picker-et" name="date-picker-et"/>'
                     . '</td>'
                     .'<td class="worked_time" data-worked-hours="'.$task['value']['workedHours'].'" data-worked-minutes="'.$task['value']['workedMinutes'].'" data-task-id="' . $task['id'] . '" data-task-name="' . $task['value']['taskName'] . '">'
                         . '<span class="my_label" rel="tooltip" title="click to edit">' . $task['workedHours'] .' '. $task['workedMinutes'] . '</span>'
                         . '<input class="date-picker-wt" name="date-picker-wt"/>'
                     . '</td>'
                     .'<td class="my_progress"><div class="progress ' . $task['progressState'] . ' progress-striped">
                             <div class="bar" style="width: ' . $task['progressBarPercent'] . '%;"></div>
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
         echo '<tr class="worked_time_line"><td colspan="4"><td class="text_align right">Worked today:</td><td class="worked_time_today">0 hours 0 minutes</td></tr>
             </tbody>
             </table>
             <h2>Completed tasks</h2>
             <table class="att_track_table table table-bordered">
                <thead>
                    <tr>
                      <th>Project</th>
                      <th>Tasks (assigned to you)</th>
                      <th>Estimated Time</th>
                      <th>Worked Time</th>
                      <th>Progress</th>
                    </tr>
               </thead>
               <tbody>';
           // no completed task is found
          $totalWorkedMinutes = 0;
          $totalWorkedHours = 0;
          
          if(count($completedTasks) == 0) echo '<tr><td colspan="6">Sorry, no completed tasks assigned to you were found...</td></tr>';
          else {
              foreach($completedTasks as $task) {
                  echo '<tr>'
                       .'<td>'. @$task['taskState']['projects']['name'] .'</td>'
                       .'<td>'. $task['value']['taskName']  .'</td>'
                       .'<td class="estimated_time" data-estimated-hours="'.$task['value']['estimatedHours'].'" data-estimated-minutes="'.$task['value']['estimatedMinutes'].'">'
                           . '<span class="my_label">' . $task['estimatedHours'] .' '. $task['estimatedMinutes'] . '</span>'
                       . '</td>'
                       .'<td class="worked_time" data-worked-hours="'.$task['value']['workedHours'].'" data-worked-minutes="'.$task['value']['workedMinutes'].'" data-task-id="' . $task['id'] . '" data-task-name="' . $task['value']['taskName'] . '">'
                           . '<span class="my_label">' . $task['workedHours'] .' '. $task['workedMinutes'] . '</span>'
                       . '</td>'
                       .'<td class="my_progress"><div class="progress ' . $task['progressState'] . ' progress-striped">
                               <div class="bar" style="width: ' . $task['progressBarPercent'] . '%;"></div>
                           </div>
                         </td>'
                       .'</tr>';
                       
                   $totalWorkedMinutes += $task['value']['workedMinutes'];
                   $totalWorkedHours += $task['value']['workedHours'];
              }
          }
          $totalWorkedHours += floor($totalWorkedMinutes / 60);
          $totalWorkedMinutes = $totalWorkedMinutes % 60;
          
          echo '<tr class="worked_time_line"><td colspan="3"><td class="text_align right">Worked in total:</td><td class="worked_time_total">'.$totalWorkedHours.' hours '.$totalWorkedMinutes.' minutes</td></tr>
              </tbody>
              </table>';
                 
        
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
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.<br/> Or you have no internet connection.</p>';
}