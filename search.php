<?php
  $con = new Mongo();
  $db = $con->codesnippets;
  $collection = $db->snippets;
  if(isset($_GET['query'])) {
    $keywords = explode(" ", $_GET['query']);
    
    for($i = 0; $i < count($keywords); $i++){
      $keywords[$i] = urlencode(strtolower($keywords[$i]));
    }
    

    $matchingSnippets = $collection->find( array("keywords" => array('$in' => $keywords) ));
    $matchingSnippets->sort(array("last_modified" => -1, "title" => 1));
  }
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
              <li><a href="index.php">Home</a></li>
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
      <h1>Code Search <?php if(isset($matchingSnippets) && $matchingSnippets->count() > 1 ){ echo '<small>' . $matchingSnippets->count() . ' results found</small>'; } ?></h1>
      <?php if(isset($matchingSnippets) && $matchingSnippets->hasNext()):?>
        <?php foreach ($matchingSnippets as $snippet): ?>
          <?php 
            $mID = new MongoId($snippet["_id"]);
            $sID = $mID->{'$id'};
          ?>
          <div class="search-result">
            <?php if(isset($snippet['links']) && count($snippet['links']) > 0): ?>
              <div class="highlighted">
                <h6>Used on</h6>
                <ul id="links">
                  <?php foreach($snippet['links'] as $link): ?>
                    <li><a class="link" href="<?php echo htmlentities(urldecode($link)); ?>" target="blank" title="Opens in new tab"><?php echo htmlentities(urldecode($link)); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <h3>
              <a title="<?php echo htmlentities(urldecode($snippet["title"])); ?>" href="snippet.php?id=<?php echo $sID; ?>">
                <?php echo htmlentities(urldecode($snippet["title"])); ?>
              </a>
            </h3>
            <p><?php echo htmlentities(urldecode($snippet['description'])); ?></p>
            
          </div>

          <hr class="clear" />
        <?php endforeach; ?>
      <?php else: ?>
        <h2>Sorry, no results</h2>
      <?php endif; ?>
    </div> <!-- /container -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>