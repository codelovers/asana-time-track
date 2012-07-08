<?php
/**
 * AsanaApi-Class connect to the asana api
 * some helper functions e.g. getWorkspaces, getProjects,...
 *
 * @author codelovers
 * @website codelovers.de
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
        // https://api.asana.com/api/1.0/workspaces/541558489761
        // https://api.asana.com/api/1.0/workspaces/541558489761/projects/
        // https://api.asana.com/api/1.0/projects/565454757638
        // https://api.asana.com/api/1.0/projects/565454757638/tasks
    }

    /////////////////////////////////////////////////
    // SETTER & GETTER & HELPER METHODS
    /////////////////////////////////////////////////
    public function getResponseCode(){
        return $this->responseCode;
    } 
    
    public function getWorkspaces(){
        return $this->apiRequest($this->workspaceUri);        
    }
    
    public function getProjects($workspaceId){
        return $this->apiRequest($this->workspaceUri.'/'.$workspaceId.'/projects');
    }
    
    public function getTasks($workspaceId, $projectId){
        return $this->apiRequest($this->projectUri.'/'.$projectId.'/tasks');
    }

    public function updateTask($taskId, $data){
        $data = array("data" => $data);
        $data = json_encode($data);
        return $this->apiRequest($this->taskUrl."/{$taskId}", $data, PUT_METHOD);
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

        $data = curl_exec($curl);
        
        // set responsCode, needed in index.php
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $data;
    }
}