<?php
  $con = new Mongo();
  $db = $con->codesnippets;
  $collection = $db->snippets;

  $newSnippets = $collection->find(array(), array("title", "last_modified"));
  $newSnippets->sort(array("last_modified" => -1, "title" => 1));

  $mostViewdSnippets = $collection->find(array(), array("title", "views"));
  $mostViewdSnippets->sort(array("views" => -1, "title" => 1));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Code Snippets</title>
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
              <li class="active"><a href="index.php">Home</a></li>
              <li><a href="snippet.php">Add New</a></li>              
            </ul>
          </div><!--/.nav-collapse -->
          <form class="navbar-search pull-left" action="search.php" method="get">
            <input type="text" name="query" class="search-query" placeholder="Search">
          </form>
        </div>
      </div>
    </div>
    <div class="container">
      <h1>Code Snippets <small>All your code are belong to us!</small></h1>
      <?php if($newSnippets->hasNext() || $mostViewdSnippets->hasNext()): ?>
        <div class="row">
          <div class="span6">
            <?php if($newSnippets->hasNext()): ?>
              <h2>Latest</h2>            
              <table class="table table-condensed">
                <thead>
                  <tr>
                    <th>Name</th><th>Last Modified</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($newSnippets as $snippet): ?>              
                  <?php 
                    $mID = new MongoId($snippet["_id"]);
                    $sID = $mID->{'$id'};
                  ?>
                  <tr>
                    <td><a title="<?php echo htmlentities(urldecode($snippet["title"])); ?>" href="snippet.php?id=<?php echo $sID; ?>"><?php echo htmlentities(urldecode($snippet["title"])); ?></a></td>
                    <td><?php echo date("d/m/Y H:i:s", strtotime($snippet["last_modified"])); ?></td>                
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>                          
            <?php endif; ?>
          </div>
          <div class="span6">
            <?php if($mostViewdSnippets->hasNext()): ?>
              <h2>Most Popular</h2>            
              <table class="table table-condensed">
                <thead>
                  <tr>
                    <th>Name</th><th>Views</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($mostViewdSnippets as $snippet): ?>              
                  <?php 
                    $mID = new MongoId($snippet["_id"]);
                    $sID = $mID->{'$id'};
                  ?>
                  <tr>
                    <td><a title="<?php echo htmlentities(urldecode($snippet["title"])); ?>" href="snippet.php?id=<?php echo $sID; ?>"><?php echo htmlentities(urldecode($snippet["title"])); ?></a></td>
                    <td><?php echo $snippet["views"]; ?></td>                
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>            
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <h2>Sorry, no snippets exist <small>You could always <a href="snippet.php" title="Create a New Snippet">Create one</a></h2>
      <?php endif; ?>
    </div> <!-- /container -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>