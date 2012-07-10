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


    // projects
    ////////////////////////////
    if($workspaceId != '' && $projectId == ''){
           
       //echo '<b>Projects:</b><br />';
       
        $result = $asana->getProjects($workspaceId);
        $result = json_decode($result);
        $counter = 1;
        foreach($result->data as $project){
            //echo '- <a href="?apiKey='. $apiKey . '&workspaceId=' . $workspaceId.'&projectId=' . $project->id . '" target="_self">' . $project->name . '</a><br />';
            $class = ($counter == 1) ? 'class="active"' : 'class=""';
            echo '<li data-project-id="' . $project->id .'"' . $class .'><a href="#tab' . $counter . '" data-toggle="tab">' . $project->name . '</a></li>';            $counter++;
        }
    }
    
    // tasks
    ////////////////////////////
    if($projectId != ''){
          
        //echo '<b>Tasks:</b><br />';
        
        $result = $asana->getTasks($projectId, $workspaceId);
        $result = json_decode($result);

        foreach($result->data as $task){

             $value = $asana->getGuessAndWorkedTime($task->name);
             $taskState = $asana->getOneTask($task->id);
             $taskName = $value['taskName'];
             $guessHours = (!empty($value['guessHours'])) ? $value['guessHours'].'h' : '0h';
             $guessMinutes = (!empty($value['guessMinutes'])) ? $value['guessMinutes'].'m' : '0m';
             $workedHours = (!empty($value['workedHours'])) ? $value['workedHours'].'h' : '0h';
             $workedMinutes = (!empty($value['workedMinutes'])) ? $value['workedMinutes'].'m' : '0m';
             
             // task must be active and your own   
             if($taskState['completed'] || $taskState['assignee'] != $userId) {
                continue;
             } else {
                //echo '- <a href="?apiKey='. $apiKey . '&projectId=' . $projectId . '&updateId=' . $task->id . '" target="_self" data-guess-hours="'. $value['guessHours'] .'" data-guess-minutes="'. $value['guessMinutes'] .'" data-worked-hours="'. $value['workedHours'] .'" data-worked-minutes="'. $value['workedMinutes'] .'">' . $task->name . '</a><br />';
                
                echo '<tr data-task-id="' . $task->id . '">'
                    .'<td>'. $taskName  .'</td>'
                    .'<td data-guess-hours="'. $guessHours .'" data-guess-minutes="'. $guessMinutes .'">'. $guessHours .' '. $guessMinutes .'</td>'
                    .'<td data-worked-hours="'. $workedHours .'" data-worked-minutes="'. $workedMinutes .'">'. $workedHours .' '. $workedMinutes .'</td>'
                    .'<td><div class="progress progress-success progress-striped active">
                            <div class="bar" style="width: 0%;"></div>
                        </div>
                      </td>'
                    .'<td class="my_timer">
                        <div class="time"></div>
                        <button class="btn btn-success" type="submit">
                            <i class="icon-white icon-play"></i> &nbsp; Start
                        </button>
                      </td>'
                    .'</tr>';
                 
             }
         }
        
    }
    
    // update
    ////////////////////////////    
    if($updateId != ''){
        $asana->updateTask($updateId);
    }
    

} else {
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.</p>';
}