<?php
 
/**
 * An example resource
 * @uri /snippet/:idhash
 */
class SnippetResource extends Resource {
    
    function get($request, $idhash){
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
        $con = new Mongo();
        $db = $con->codesnippets;
        $collection = $db->snippets;

        $mID = new MongoId($idhash);
        $snippet = $collection->findOne(array("_id" => $mID));

        
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
        
        

        //Set the response body to be our encoded array.        
        $response->body = json_encode($snippetArray);  
        
        return $response;        
    }

    function post($request, $idhash){
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
        $con = new Mongo();
        $db = $con->codesnippets;
        $collection = $db->snippets;        
        
        if(isset($request->data)  && $request->data != ""){
            $newSnippet = json_decode($request->data);
            $collection->insert($newSnippet);
            $snippet = $collection->findOne($newSnippet);
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

    function put($request, $idhash){
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
        $con = new Mongo();
        $db = $con->codesnippets;
        $collection = $db->snippets;        
        $snippetArray = array();

        if(isset($request->data)  && $request->data != ""){
            $updatedSnippet = json_decode($request->data);
            $mID = new MongoId($idhash);

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

    function delete($request, $idhash){
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        
        //Open up a connection to our snippet store
        $con = new Mongo();
        $db = $con->codesnippets;
        $collection = $db->snippets;
                
        $mID = new MongoId($idhash);

        $collection->remove(array("_id" => $mID));
        $response->body = json_encode(array("id" => $idhash, "status" => "removed"));
        

        
        return $response;
    }
}
?>
