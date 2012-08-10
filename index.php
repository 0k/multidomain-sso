<?php

require_once "config.php";
require_once "oeauth.php";

$oe = new OEAuth($config["oe"]["url"], $config["oe"]["dbname"]);

$is_auth = $oe->is_auth();
$was_auth = $is_auth;
if ($is_auth) {
  if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "logout") {
    $oe->deauthenticate();
    $is_auth = False;
  };
} else {
  $oe->authenticate($_POST);
  $is_auth = $oe->is_auth();
}


$authentication_msg = "<p>You are " . ($is_auth?"<span class='green'>authentified</span>":"unknown") .
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

span.green {
color: green;
}

    </style>

    <?php echo $oe->js_code; ?>

  </head>
  <body>

    <?php echo $authentication_msg; ?>

    <form method="post">
      <?php echo $form_content; ?>
    </form>

    <?php

    if ($is_auth) {
      echo "OE DATA ACCESS: <pre>";
      $partners = $oe->read(array(
          'model' => 'res.partner',
          'fields' => array('name', 'city'),
      ));

      foreach($partners['records'] as $partner) {
        echo "<li>" . $partner["name"] . " - " . $partner["city"] . "</li>";
      }
      echo "</pre>";
    };
    ?>
  </body>
</html>
