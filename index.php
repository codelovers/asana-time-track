<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Asana Time Track</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An extension for the popular task management tool Asana. This Extension provides you with time track for your Asana-Tasks.">
    <meta name="author" content="codelovers">

    <!-- Le styles -->
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="img/ico" href="img/favicon.ico">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
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
          <a class="brand" href="#">Asana Time Track</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li id="change-workspace"><a data-toggle="modal" href="#start-modal">Change Workspace </a></li>
              <li><a data-toggle="modal" href="#about-modal">About</a></li>
            </ul>
          </div><!--/.nav-collapse -->
          <div class="nav-collapse pull-right">
            <ul class="nav">
                <li>
                    <a href="#"><label class="checkbox">
                      <input id="animate" type="checkbox" value="" checked>
                      Progress Animation
                    </label></a>
                </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="start-modal">
      <div class="modal-header">
        <h3>Insert ASANA-API-KEY</h3>
      </div>
      <div class="modal-body">
        <form id="api-key-form" class="well form-horizontal" validate>
          <div class="control-group">
            <div class="controls">
              <input type="text" id="api-key" required placeholder="Enter API-KEY here">
              <span class="help-inline">
                  <button id="api-key-edit" class="btn btn-primary">
                      <i class="icon-edit icon-white"></i>
                      Edit
                  </button>
                  <button id="api-key-delete" class="btn btn-danger">
                      <i class="icon-trash icon-white"></i>
                      Delete
                  </button>
                  <button id="api-key-refresh" class="btn">
                      <i class="icon-refresh"></i>
                      Refresh
                  </button>
                  <img class="ajax_img" src="img/ajax-loader.gif"/>
             </span>
             <p class="hint_api_key">Find you Asana-Api-Key <a href="http://app.asana.com/-/account_api" target="_blank">here</a>. The Asana-Api-Key is only saved on your computer (Cookie).</p>
            </div>
          </div>
          <button id="start-send" type="submit" class="btn">Submit</button>
        </form>
        <div id="workspace-container" class="row-fluid">
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
    
    <div class="modal fade" id="about-modal">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>Who made this awesome tool?</h3>
      </div>
      <div class="modal-body">
        <p>
            We did, because we love asana but missed a time tracking feature.<br/>
            So we created one on our own, which works perfectly well with asana together.
            <br/><br/>
            If you want to know more about us, visit our Website <a href="http://codelovers.de">codelovers.de</a>.
            <br/><br/>
            <a href="https://twitter.com/we_love_code" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @we_love_code</a>
        </p>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
      </div>
    </div>
    
    <div class="container">

    <h1 class="workspace_caption">Workspace</h1>
    <div id="loader-wrapper">
        <button id="workspace-refresh" class="btn">
            <i class="icon-refresh"></i>
            Refresh
        </button>
        <img class="ajax_img" src="img/ajax-loader.gif"/>
    </div>
    <div id="track-table">
      <table class="att_track_table table table-bordered">
        <thead>
          <tr>
            <th>Project</th>
            <th>Tasks (assigned to you)</th>
            <th>Estimated Time</th>
            <th>Worked Time</th>
            <th>Progress</th>
            <th>Timer</th>
          </tr>
        </thead>
        <tbody>
           <!-- content rendered here -->
        </tbody>
      </table>
    </div>
    </div> <!-- /container -->
    <div class="modal-backdrop"></div>

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/js-libs/jquery/jquery.js"></script>
    <script src="js/js-libs/bootstrap/bootstrap-modal.js"></script>
    <script src="js/js-libs/bootstrap/bootstrap-tooltip.js"></script>
    <script src="js/js-libs/jquery/jquery.cookie.js"></script>
    <script src="js/js-libs/jquery/jquery.stopwatch.js"></script>
    <script src="js/js-libs/mobiscroll-2.0.custom.min.js"></script>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    <script src="js/scripts.js"></script>
  </body>
</html>