<?php
include_once 'classes/SnippetDownloader.php';

try{
	$con = new Mongo();
    $db = $con->codesnippets;
    $collection = $db->snippets;

    $isError = true;

	if(isset($_GET['id']) && $_GET['id'] != ""){
	    $mID = new MongoId($_GET['id']);
                
    	$snippet = $collection->findOne(array("_id" => $mID));        

        if(count($snippet['files']) > 0){
            $sd = new SnippetDownloader();
            $sd->createZip($snippet);            
        }else{
        	header("Location: index.php");
			exit();	
        }
	}else{
		header("Location: index.php");
		exit();
	}

}catch (MongoConnectionException $e){
    header("Location: error.php");    
    exit();
}catch (MongoException $e){
    header("Location: error.php");
    exit();
}
?>