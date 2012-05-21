<?php
 
/**
 * An example resource
 * @uri /api/snippets
 */
class SnippetResource extends Resource {
    
    function get($request) {        
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
		$con = new Mongo();
		$db = $con->codesnippets;
		$collection = $db->snippets;

		//Pull out our sinippets and order by newest first and then title
		$newSnippets = $collection->find();
		$newSnippets->sort(array("last_modified" => -1, "title" => 1));

		//Create an array of snippets.  
		//Note: We need to do this so we can encode it back into JSON
		//      to send down the wire.
		$snippetsList = array();
		foreach ($newSnippets as $snippet){

	    	$mID = new MongoId($snippet["_id"]);
		    $sID = $mID->{'$id'};
		    $snippetArray = array('id' => $sID, 
		    			   	      'title' => $snippet["title"],
		    				      'description' => $snippet["description"],
		    				      'links' => $snippet['links'],
		    				      'files' => $snippet['files'],
		    				      'dateCreated' => $snippet["date_created"],
		    				      'lastModified' => $snippet["last_modified"], 
		    				      'views' => $snippet["views"]);
		    $snippetsList[] = $snippetArray;		
		}

		//Set the response body to be our encoded array.
		$response->body = json_encode($snippetsList);		  
        return $response;        
    }

}
?>
