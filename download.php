<?php
try{
	$con = new Mongo();
    $db = $con->codesnippets;
    $collection = $db->snippets;

    $isError = true;

	if(isset($_GET['id']) && $_GET['id'] != ""){
	    $mID = new MongoId($_GET['id']);
                
    	$snippet = $collection->findOne(array("_id" => $mID));
        

        if(count($snippet['files']) > 0){
            downloadFileZip($snippet);
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

function downloadFileZip($snippet){
	$fileArray = $snippet['files'];
    $snippetTitle = htmlentities(urldecode($snippet["title"]));
    $description = htmlentities(urldecode($snippet["description"]));
    $links = $snippet["links"];
    $dateCreated = date("d-m-Y h:i:s", strtotime($snippet["date_created"]));
    $lastModified = date("d-m-Y h:i:s", strtotime($snippet["last_modified"]));
    $views = $snippet["views"];

	//Create base directory
	$baseDir = uniqid('tmp/dl') . "/";
	@mkdir($baseDir);

	//Create the zip archive
	$zip = new ZipArchive();
	$zipname = "tmp/snippet-doanload-" . uniqid() . ".zip";

	if ($zip->open($zipname, ZIPARCHIVE::CREATE)!==TRUE) {
	    exit("cannot open <$zipname>\n");
	}

	//for each file, create it temp on disk then add to the zip
    foreach ($fileArray as $file) {
        $filename = urldecode($file['filename']);
        
        $pos = strrpos($filename, "/");
		
		if ($pos !== false) { // note: three equal signs
			$dir = $baseDir . substr($filename, 0, $pos);
			@mkdir($dir, 0777, true);
		}
		
        file_put_contents($baseDir . $filename, urldecode($file['filecontent']), FILE_APPEND);
        //Add file to zip but change /tmp/38943u99843t89yh/css/style.css to /MySnippet/css/style.css
        $zip->addFile($baseDir . $filename, str_ireplace($baseDir, $snippetTitle . "/", $baseDir . $filename));
    }    

    //Build and add readme
    $readme = $snippetTitle . "\r\n";
    for($i = 0; $i < strlen($snippetTitle); $i++ ){
    	$readme .= "=";
    }
    $readme .= "\r\n";
    $readme .= $description . "\r\n\r\n";
    $readme .= "Date Created: " . $dateCreated . "\r\n";
    $readme .= "Last Modified: " . $lastModified . "\r\n";
    $readme .= "Views: " . $views . "\r\n\r\n";

    if(count($links) > 0){
	    $readme .= "Example Links\r\n=============\r\n";
	    foreach($links as $link){
	    	$readme .= urldecode($link) . "\r\n";
		}
	}

	$readme .= "\r\n";
	$readme .= "DISCLAIMER\r\n=======\r\nDouble check the code before you use it!  If the internet falls apart because you've blindly used the code in this download, it's on you pal.  Have a nice day.";
	$readme .= "\r\n";

	file_put_contents($baseDir . "README.txt", $readme, FILE_APPEND);
	$zip->addFile($baseDir . "README.txt", str_ireplace($baseDir, $snippetTitle . "/", $baseDir . "README.txt"));

    
    $zip->close();

    //remove all our temp files and just leave the zip
    recursiveDelete($baseDir);

    if ($fd = fopen ($zipname, "r")) {
	    $fsize = filesize($zipname);
	    $path_parts = pathinfo($zipname);	    
	    $downloadName = strtolower(str_ireplace(" ", "-", $snippetTitle). ".zip");
        
        header("Content-type: application/zip"); // add here more headers for diff. extensions
        header("Content-Disposition: attachment; filename=$downloadName"); // use 'attachment' to force a download
	    header("Content-length: $fsize");
	    header("Cache-control: private"); //use this to open files directly

	    while(!feof($fd)) {
	        $buffer = fread($fd, 2048);
	        echo $buffer;
	    }
	}
	fclose ($fd);
	exit();
}
	

/**
 * Delete a file or recursively delete a directory
 *
 * @param string $str Path to file or directory
 */
function recursiveDelete($str){
    if(is_file($str)){
        return @unlink($str);
    }elseif(is_dir($str)){
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path){
            recursiveDelete($path);
        }
        return @rmdir($str);
    }
}
?>