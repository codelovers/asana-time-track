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

// initalize
$asana = new AsanaApi($apiKey); 
$result = $asana->getWorkspaces();

// check if everything works fine
if($asana->getResponseCode() == '200' && $result != '' ){
	    
    
    // workspaces
    ////////////////////////////
    if($workspaceId == '' && $projectId == ''){
        
       echo '<b>Workspaces:</b><br />';

    	$resultJson = json_decode($result);
    	// $resultJson contains an object in json with all projects
    	foreach($resultJson->data as $workspace){
    		echo '- <a href="?apiKey='.$apiKey.'&workspaceId=' . $workspace->id.'" target="_self">'. $workspace->name .'</a><br>';
    	}
    }


    // projects
    ////////////////////////////
    if($workspaceId != ''){
           
       echo '<b>Projects:</b><br />';
       
        $result = $asana->getProjects($workspaceId);
        $result = json_decode($result);
        foreach($result->data as $project){
            echo '- <a href="?apiKey='. $apiKey . '&projectId=' . $project->id . '" target="_self">' . $project->name . '</a><br />';
        }
    }
    
    // tasks
    ////////////////////////////
    if($projectId != ''){
          
        echo '<b>Tasks:</b><br />';
        
        $result = $asana->getTasks($projectId);
        $result = json_decode($result);
        foreach($result->data as $task){
            echo '- ' . $task->name . '<br />';
        }
        
    }

} else {
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.</p>';
}