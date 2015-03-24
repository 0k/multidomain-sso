<?php

require_once "vendor/autoload.php";
require_once "config.php";
require_once "oeauth.php";

$oe = new OEAuth($config["oe"]["url"], $config["oe"]["dbname"]);

$bad_login = False;
$is_auth = $oe->is_auth();
$was_auth = $is_auth;
if ($is_auth) {
  if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "logout") {
    $oe->deauthenticate();
    $is_auth = False;
  };
} else {
  if (isset($_POST["action"])) {
    $oe->authenticate($_POST);
    $is_auth = $oe->is_auth();
    if (!$is_auth) $bad_login = True;
  };
}

$authentication_msg = "<p id='status'>You are " . ($is_auth?"<span class='green'>authentified</span>":"unknown") .
     " here, on  " . $_SERVER['HTTP_HOST'] ."</p>";

if (!$is_auth) {
  // refresh toward a login page/widget
  $form_content = "login: <input type='text' name='login' /><br/>";
  $form_content .= "password: <input type='text' name='password' /><br/>";
  $form_content .= "<input type='submit' name='action' value='login' />";
} else {
  $form_content = "<input type='submit' name='action' value='logout' />";
}


/*
 * HTML CODE
 */


?><html>
  <head>
    <title>Serving from <?php echo $_SERVER['HTTP_HOST']; ?></title>

    <script src="/lib/jquery/jquery.js" type="text/javascript" > </script>

    <style type="text/css">

body, div {
  font-family: arial;
  padding: 5px;
}

div#domain-list {
  float: right;
  border: #aaa 1px solid;
  padding: 5px;
  font-size: 80%;
  background-color: #eee;
}

div#session {
  float: left;
  background-color: #ff8;
}

span.green {
  color: green;
}

div.clear {
  clear: both;
}

em.bad-login {
  font-weight: bold;
color: red;
}

    </style>

    <?php echo $oe->js_code_for_propagate(); ?>

  </head>
  <body>

    <?php echo $authentication_msg; ?>

    <div id="domain-list">These domain shares authentication info:
    <?php
    foreach($config["urls"] as $url) {
      echo "<li><a href='$url'>$url</a></li>";
    }
    ?>
    </div>

    <div id="session">
    <?php if ($bad_login) echo "<em class='bad-login'>Bad login !</em>"; ?>
    <form method="post">
      <?php echo $form_content; ?>
    </form>
    </div>
    <div class="clear" />
    <?php

    if ($is_auth) {
      echo "Sample OpenERP query which requires login: <pre>";
      $partners = $oe->read(array(
          'model' => 'res.partner',
          'fields' => array('name', 'city'),
      ));

      foreach($partners['records'] as $partner) {
        echo "<li>" . $partner["name"] . " - " . $partner["city"] . "</li>";
      }
      echo "</pre>";
    } else {
      echo "<em>You are not authorized, you should try to login with 'admin', and 'demo' as password.</em>";
    };

    ?>


  </body>
</html>
