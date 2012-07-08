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

// get api key
$apiKey = $_GET['apiKey'];

// initalize
$asana = new AsanaApi($apiKey); 
$result = $asana->getWorkspaces();
//$resultP = $asana->getProjects(541558489761);
// check if everything works fine
if($asana->getResponseCode() == '200' && $result != '' ){
	    
	$resultJson = json_decode($result);
    
	// $resultJson contains an object in json with all projects
	foreach($resultJson->data as $workspace){
		echo '<b>' . $workspace->id.' - '. $workspace->name .'</b><br>';
        
        $result = $asana->getProjects($workspace->id);
        $result = json_decode($result);
        foreach($result->data as $project){
            echo '####<b>' . $project->id . ' - ' . $project->name . '</b><br />';
            
            $result = $asana->getTasks($workspace->id, $project->id);
            $result = json_decode($result);
            foreach($result->data as $task){
                echo '____' . $task->name . '<br />';
            }
            
        }
        
	}

} else {
    echo '<p>ERROR: Something went wrong! Maybe your asana api key does not fit.</p>';
}