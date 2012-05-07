<?php
    try{
    	$con = new Mongo();
        $db = $con->codesnippets;
        $collection = $db->snippets;

        $isError = true;

        //Retreive the requested snippet
    	if(isset($_GET['id']) && $_GET['id'] != ""){
    	    $mID = new MongoId($_GET['id']);
    	    $snippet = $collection->findOne(array("_id" => $mID));
    	    
    	}

        //If we get a post we're modifying the snippet somehow
        if(isset($_POST['id']) && $_POST['id'] != ""){
            $mID = new MongoId($_POST['id']);   
            if(isset($_POST['update-snippet'])){ //Update Snippet
                $msg = "Cound not save...";
                if($_POST['update-snippet'] != ""){
                    $updatedSnippet = json_decode($_POST['update-snippet']);
                    $collection->update(array("_id" => $mID), $updatedSnippet);
                    $snippet = $collection->findOne($updatedSnippet);
                    $msg = "Updated the snippet";               
                }
            }elseif(isset($_POST['delete-snippet'])){ //Remove snippet                         
                $collection->remove(array("_id" => $mID));
                header("Location: index.php");
                exit();
            }
        }elseif(isset($_POST['new-snippet'])){        
            $msg = "Cound not save...";
            if($_POST['new-snippet'] != ""){  
                $snippet = json_decode($_POST['new-snippet']);
                $collection->insert($snippet);
                $snippet = $collection->findOne($snippet);
                $mID = new MongoId($snippet["_id"]);      
                $msg = "Added a new snippet to the collection!";                  
            }        
    	}

        if(isset($snippet)){ //pull out all our info...
            $title = htmlentities(urldecode($snippet["title"]));
            $description = htmlentities(urldecode($snippet["description"]));
            $links = $snippet['links'];
            $files = $snippet['files'];
            $dateCreated = $snippet["date_created"];
            $lastModified = $snippet["last_modified"];
            $views = $snippet["views"];
            $isError = false;

            //If we're viewing a snippet and not updating it, update the views fieled
            if((isset($_GET['id']) && $_GET['id'] != "") &&  (!isset($_POST['update-snippet']))){            
                $collection->update(array("_id" => $mID), array('$inc' => array("views" => 1)));
            }
        }
    }catch (MongoConnectionException $e){
        header("Location: error.php");
        exit();
    }catch (MongoException $e){
        header("Location: error.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title><?php echo (isset($mID)) ? $title. " | Code Snippets" : "Code Snippets"; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">     
    <link href="favicon.ico" rel="icon" type="image/x-icon" />
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->    
    </head>
    <body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">                    
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="index.php">Snippets</a>
                    <div class="nav-collapse">
                        <ul class="nav">
                            <li><a href="index.php">Home</a></li>
                            <li<?php if(!isset($mID)){ echo ' class="active"'; }?>><a href="snippet.php">Add New</a></li>              
                        </ul>
                    </div><!--/.nav-collapse -->
                    <form class="navbar-search pull-left" action="search.php" method="get">
                        <input type="text" name="query" class="search-query" placeholder="Search">
                    </form>
                </div>
            </div>
        </div>
        <div class="container">
        	<?php if(isset($msg) && $msg != ""): ?>
                <?php if($isError):?>
                    <div class="alert alert-error fade in">
                <?php else:?>
                    <div class="alert alert-success fade in">
                <?php endif;?>
                <a class="close" data-dismiss="alert" href="#">&times;</a>
                <h2 class="alert-heading"><?php echo ($isError) ? "Error!" : "Success!";?></h2>
                <strong><?php echo ($isError) ? "Error: " : "";?></strong>&nbsp;<?php echo $msg; ?>     
            </div>
            <?php endif; ?> 
            <div class="alert alert-error fade in" id="validation-error">                
                <a class="close" data-dismiss="alert" href="#">&times;</a>
                <h2 class="alert-heading">Woah there! <small>We'll be needing a title for this snippet</small></h2>
            </div>
    		<div id="main-container" >                
                <div class="row">
                    <div class="span8">                                                                     
                        <div class="well">
                            <div id="snippet-list">
                                <?php if(isset($mID)): ?>
                                    <a id="download-button" class="btn btn-large button-secondary" target="_blank" href="download.php?id=<?php echo $mID->{'$id'}; ?>"><i class="icon-download"></i>&nbsp;Download</a>
                                <?php endif; ?>
                                <h1>Snippet Editor <small><?php echo (isset($title)) ? "Snippet: <b>$title</b>" : "Snippet: <b>New Snippet</b>" ; ?></small></h1>
                                <?php $fileCount = 0; ?>
                                <?php if(isset($files) && count($files) > 0): ?>
	                                <?php foreach ($files as $file): ?>
	                                	<div class="snippet-file">
		                                    <input type="hidden" class="editornumber" value="<?php echo $fileCount; ?>"/>
		                                    <input type="text" class="span3 file-name" value="<?php echo htmlentities(urldecode($file['filename'])); ?>">
		                                    <div class="aceeditor" id="editor<?php echo $fileCount; ?>"><?php echo htmlentities(urldecode($file['filecontent'])); ?></div>
		                                </div>
		                                <?php $fileCount++; ?>
	                                <?php endforeach; ?>	                                
                                <?php else: ?>
                                    <div class="snippet-file">
                                        <input type="hidden" class="editornumber" value="<?php echo $fileCount; ?>"/>
                                        <input type="text" class="span3 file-name" placeholder="admin/functions.php">
                                        <div class="aceeditor" id="editor0"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#" id="add-new-file" class="btn btn-success"><i class="icon-plus icon-white"></i>&nbsp;Add File</a>        
                        </div>                        
                    </div>
                    <div class="span4">
                        <div id="right-col" class="well">
                            <h1>Snippet Info</h1>                        
                            <input type="text" name="title" id="title" <?php echo (isset($title)) ? 'value="' . $title . '"' : 'placeholder="Name"'; ?>>
                            <textarea name="description" id="description" rows="6"<?php if(!isset($description)){ echo 'placeholder="Description"'; } ?>><?php if(isset($description)){ echo $description; } ?></textarea>
                            <div id="meta-info">
                                <?php if(isset($mID)): ?>
                                    <b>Date Created:</b> <?php echo date("d/m/Y h:i:s", strtotime($dateCreated)); ?><br/>
                                    <b>Last Modified:</b> <?php echo date("d/m/Y h:i:s", strtotime($lastModified)); ?><br/>
                                    <b>Views:</b> <?php echo ($views == 1) ? "Viewed once" : "Viewed $views times."; ?>
                                <?php endif; ?>
                            </div>
                            <div id="links-container">
                                <h3>Sites that use this snippet</h3>
                                <ul id="links">
                                    <?php if(isset($links) && count($links) > 0): ?>
    	                            	<?php foreach($links as $link): ?>
    	                            		<li><a class="link" href="<?php echo htmlentities(urldecode($link)); ?>" target="blank" title="Opens in new tab"><?php echo htmlentities(urldecode($link)); ?></a> <a href="#" class="delete-link">(delete)</a></li>
    	                            	<?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                                <input type="text" name="link" id="link" placeholder="http://www.mysupersite.me/">
                                <a href="#" id="add-new-link" class="btn btn-success"><i class="icon-plus icon-white"></i>Add Link</a>     

                                <input type="hidden" name="views" id="views" value="<?php echo (isset($views) && is_numeric($views)) ? $views : 1 ; ?>"/>
                                <input type="hidden" name="dateCreated" id="dateCreated" value="<?php echo (isset($dateCreated)) ? $dateCreated : date("Y-m-d h:i:s") ; ?>"/>
                                <input type="hidden" name="lastModified" id="lastModified" value="<?php echo date("Y-m-d h:i:s"); ?>"/>
                                
                            </div>
                            <div id="update-buttons">
                                <a href="#" id="save" class="btn btn-primary"><?php echo (isset($mID)) ? '<i class="icon-refresh icon-white"></i>&nbsp;Update' : '<i class="icon-ok icon-white"></i>&nbsp;Save'  ; ?></a>
                                <?php if(isset($mID)): ?>
                                <a href="#" id="delete" class="btn btn-danger"><i class="icon-trash icon-white"></i>&nbsp;Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>    			
		</div> <!-- /container -->
        <?php if(isset($mID)): ?>
    		<form action="snippet.php" method="post" id="snippet-form">
                <input type="hidden" id="id" name="id" value="<?php echo $mID->{'$id'}; ?>"/>
                <input type="hidden" id="snippet" name="update-snippet"/>
            </form>
            <form action="snippet.php" method="post" id="delete-form">
                <input type="hidden" id="id" name="id" value="<?php echo $mID->{'$id'}; ?>"/>
                <input type="hidden" id="snippet" name="delete-snippet"/>
            </form>
        <?php else: ?>
            <form action="snippet.php" method="post" id="snippet-form">
                <input type="hidden" id="snippet" name="new-snippet"/>
            </form>
        <?php endif; ?>
        <script src="js/jquery.js"></script>        
        <script src="js/bootstrap.min.js"></script>
        <script src="js/ace.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-css.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-html.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-javascript.js" type="text/javascript" charset="utf-8"></script>        
        <script src="js/mode-json.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-php.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-sh.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-sql.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-text.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/mode-xml.js" type="text/javascript" charset="utf-8"></script>        
        <script src="js/theme-solarized_dark.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/snippets.js" type="text/javascript" charset="utf-8"></script>        
	</body>
</html>
