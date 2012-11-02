<?php
/**
 * AsanaApi-Class connect to the asana api
 * some helper functions e.g. getWorkspaces, getProjects,...
 *
 * @author codelovers
 * @author codelovers.de
 * @version 1.0 (2012_07_07)
 * @package asana_track_time
 */

class AsanaApi {

    // ##############################################################################################
    // CLASS VARIABLES & CONSTANTS
    // ##############################################################################################
    // variables
    private $apiKey, $uri, $workspaceUri, $responseCode;

    // set constants
    const PUT_METHOD  = 2;
    const GET_METHOD  = 3;
    
    // ##############################################################################################
    // CONSTRUCTOR
    // ##############################################################################################
    public function __construct($apiKey){
        
        // : away, append it
        if(substr($apiKey, -1) != ":"){
          $apiKey .= ":"; 
        } 
        
        // initialize needed values
        $this->apiKey = $apiKey;        
        $this->uri = "https://api.asana.com/api/1.0/";
        $this->workspaceUri = $this->uri."workspaces"; // 
        $this->projectUri = $this->uri."projects"; 
        $this->taskUri = $this->uri."tasks";
        $this->userUri = $this->uri."users";
    }

    // ##############################################################################################
    // SETTER & GETTER & HELPER METHODS
    // ##############################################################################################
    public function getUserId(){
        $resultJson = json_decode($this->apiRequest($this->userUri.'/me'));
        return $resultJson->data->id;
    }
    
    public function getResponseCode(){
        return $this->responseCode;
    } 
    
    public function getWorkspaces(){
        return $this->apiRequest($this->workspaceUri);
    }
    
    public function getTasks($workspaceId){
        return $this->apiRequest($this->workspaceUri.'/'.$workspaceId.'/tasks?assignee=me');
    }
    
    public function getOneTask($taskId){
        $resultJson = json_decode($this->apiRequest($this->taskUri.'/'.$taskId));
        
        $castIntoArray = (array) @$resultJson->data->projects[0];
        
        $array = array ( 'completed' => $resultJson->data->completed,
                         'assignee' => $resultJson->data->assignee->id,
                         'projects' => $castIntoArray
                       );
        
        return $array;
    }

    public function updateTask($taskId, $workedHours, $workedMinutes, $estimatedHours, $estimatedMinutes, $taskName){

        $data = array( "name" => $taskName ." [ET: " . $estimatedHours . "h " . $estimatedMinutes . "m] [WT: " . $workedHours . "h " . $workedMinutes . "m]");        
        $data = array("data" => $data);
        $data = json_encode($data);
        
        return $this->apiRequest($this->taskUri.'/'.$taskId , $data, self::PUT_METHOD);
    }
    
    public function getEstimatedAndWorkedTime($taskName){
        $estimatedTimeHours = 0;
        $estimatedTimeMinutes = 0;
        $workedTimeHours = 0;
        $workedTimeMinutes = 0;
        $workedTime = 0;
        
        $pattern = "/\[ET\: (\d+)h (\d+)m\] \[WT\: (\d+)h (\d+)m\]$/";
        
        if(preg_match($pattern, $taskName, $matches)) {
            
            // estimated time
            $estimatedTimeHours = $matches[1];
            $estimatedTimeMinutes = $matches[2];
            
            // worked time
            $workedTimeHours = $matches[3];
            $workedTimeMinutes = $matches[4];
            
            // worked time in sec
            $workedTime = $workedTimeHours * 60 * 60 * 1000;
            $workedTime += $workedTimeMinutes * 60 * 1000;
            
            $taskName = preg_replace($pattern, "", $taskName);
        }
        
        $array = array ( 'taskName' => $taskName,
                         'estimatedHours' => $estimatedTimeHours,
                         'estimatedMinutes' => $estimatedTimeMinutes,
                         'workedHours' => $workedTimeHours,
                         'workedMinutes' => $workedTimeMinutes,
                         'workedTimeSec' => $workedTime
                       );
        
        return $array;
        
    }
    
    // ##############################################################################################
    // ASK ASANA API AND RETURN DATA
    // ##############################################################################################
    private function apiRequest($url, $givenData = null, $method = self::GET_METHOD){

        // ask asana api and return data
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // don´t print json-string
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Send as JSON

        if($method == self::PUT_METHOD){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $givenData);
        }
        
        $data = curl_exec($curl);
        
        // set responsCode, needed in index.php
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $data;
    }
}