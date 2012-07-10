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

    /////////////////////////////////////////////////
    // CLASS VARIABLES & CONSTANTS
    /////////////////////////////////////////////////
    
    // variables
    private $apiKey, $uri, $workspaceUri, $responseCode;

    // set constants
    const POST_METHOD = 1;
    const PUT_METHOD  = 2;
    const GET_METHOD  = 3;    
    
    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////    
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
        // https://api.asana.com/api/1.0/workspaces/541558489761
        // https://api.asana.com/api/1.0/workspaces/541558489761/projects/
        // https://api.asana.com/api/1.0/projects/565454757638
        // https://api.asana.com/api/1.0/projects/565454757638/tasks
        // https://api.asana.com/api/1.0/tasks 
    }

    /////////////////////////////////////////////////
    // SETTER & GETTER & HELPER METHODS
    /////////////////////////////////////////////////
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
    
    public function getProjects($workspaceId){
        return $this->apiRequest($this->workspaceUri.'/'.$workspaceId.'/projects');
    }
    
    public function getTasks($projectId, $workspaceId){
        return $this->apiRequest($this->projectUri.'/'.$projectId.'/tasks?assignee=me');
    }
    
    public function getOneTask($taskId){
        $resultJson = json_decode($this->apiRequest($this->taskUri.'/'.$taskId.'?opt_fields=completed,assignee'));

        $array = array ( 'completed' => $resultJson->data->completed,
                         'assignee' => $resultJson->data->assignee->id
                       );
        
        return $array;
    }

    public function updateTask($taskId){
        
        // don´t change any task
        // just for playing around 
        return;
        
        $data = array( "name" => "hurra hurra [GT: 5h 4m] [WT: 3h 2m]");        
        $data = array("data" => $data);
        $data = json_encode($data);
        
        return $this->apiRequest($this->taskUri.'/'.$taskId , $data, PUT_METHOD);
    }
    
    public function getGuessAndWorkedTime($data){
        $data = explode('[', $data);  
        $data[1] = str_replace('GT:', '', $data[1]);
        $data[2] = str_replace('WT:', '', $data[2]);
        $data[1] = trim(substr($data[1], 0, -1)); // guess time
        $data[2] = trim(substr($data[2], 0, -1)); // worked time
        
        // guess time
        $explodeString = explode('h', $data[1]);
        $guessTimeHours = $explodeString[0];
        $guessTimeMinutes = trim(substr($explodeString[1], 0, -2));

        // worked time
        $explodeString = explode('h', $data[2]);
        $workedTimeHours = $explodeString[0];
        $workedTimeMinutes = trim(substr($explodeString[1], 0, -1));
        
        $array = array ( 'guessHours' => $guessTimeHours,
                         'guessMinutes' => $guessTimeMinutes,
                         'workedHours' => $workedTimeHours,
                         'workedMinutes' => $workedTimeMinutes
                       );
        
        return $array;
        
    }
    
    /////////////////////////////////////////////////
    // ASK ASANA API AND RETURN DATA
    /////////////////////////////////////////////////
    private function apiRequest($url, $givenData = null, $method = GET_METHOD){

        // ask asana api and return data
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // don´t print json-string
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Send as JSON

        //    
        if($method == PUT_METHOD){
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