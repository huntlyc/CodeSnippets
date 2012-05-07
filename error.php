<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Hotscot Snippets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">    
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
      <h1>Error!</h1>
      <p>Sorry man, looks like something has gone wrong...</p>
      <p>For your troubles, here's some non-flash pacman, source available on <a href="https://github.com/daleharvey/pacman" title="https://github.com/daleharvey/pacman" target="_blank">Github</a></p>
      <div style="height:450px;width:342px;margin:20px auto;"id="pacman"></div>
    </div> <!-- /container -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>    
    <script src="js/pacman.js"></script>
    <script src="js/modernizr-1.5.min.js"></script>

    <script>

      var el = document.getElementById("pacman");

      if (Modernizr.canvas && Modernizr.localstorage && 
          Modernizr.audio && (Modernizr.audio.ogg || Modernizr.audio.mp3)) {
        window.setTimeout(function () { PACMAN.init(el, "./"); }, 0);
      } else { 
        el.innerHTML = "Sorry, needs a decent browser<br /><small>" + 
          "(firefox 3.6+, Chrome 4+, Opera 10+ and Safari 4+)</small>";
      }
    </script>
  </body>
</html>