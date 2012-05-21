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

    function post($request){
    	$response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
		$con = new Mongo();
		$db = $con->codesnippets;
		$collection = $db->snippets;
		$snippetArray = array("snr" => $_POST['new-snippet']);
		
        if($_POST['new-snippet'] != ""){  
            $snippet = json_decode($_POST['new-snippet']);
            $collection->insert($snippet);
            $snippet = $collection->findOne($snippet);
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
        }

        $response->body = json_encode($snippetArray);
        return $response;
    }

    function put($request){
    	$response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
		$con = new Mongo();
		$db = $con->codesnippets;
		$collection = $db->snippets;
		$_PUT = array();
		parse_str($request->data, $_PUT);
		//$snippetArray = array("snr" => $request->data);
		$snippetArray = array("snr" => $_PUT['id']);
		
        if(isset($_PUT['data'])  && $_PUT['data'] != ""){  
            
            $updatedSnippet = json_decode($_PUT['data']);
            $mID = new MongoId($_PUT['id']);

            $collection->update(array("_id" => $mID), $updatedSnippet);
            $snippet = $collection->findOne($updatedSnippet);
            
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
		    				      
        }

        $response->body = json_encode($snippetArray);
        return $response;
    }

    function delete($request){
    	$response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
		$con = new Mongo();
		$db = $con->codesnippets;
		$collection = $db->snippets;
		$_DELETE = array();
		parse_str($request->data, $_DELETE);
		//$snippetArray = array("snr" => $request->data);
		$snippetArray = array("snr" => $_DELETE['id']);
		$response->body = json_encode($snippetArray);
		
        if(isset($_DELETE['id'])){  
            $mID = new MongoId($_DELETE['id']);

             $collection->remove(array("_id" => $mID));
             $response->body = json_encode(array("id" => $_DELETE['id'], "status" => "removed"));
        }

        
        return $response;
    }
}
?>
