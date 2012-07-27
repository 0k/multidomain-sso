<?php

require_once "common.php";


session_start();

$session_credentials = get_credential_from_php_session();
$final_credentials = update_credential_with_request($session_credentials, $_POST);

$was_auth = validate_authentication_by_credentials($session_credentials);
$is_auth = validate_authentication_by_credentials($final_credentials);

$propagate_msg  = "";
$propagate_code  = "";

if ($was_auth != $is_auth) { // changed auth status !
  $propagate_msg  = "propagating authentication changes... <span id='ans'></span>";
  $propagate_code = "<script type='text/javascript'>\n" . js_code_for_get_session_id($is_auth) . "

    $(document).ready(function () {
        propagate_authentication_status('http://" . $_SERVER["HTTP_HOST"] . "').then(function() {
           $('span#ans').html('DONE');
        });
    });

</script>";

  if ($is_auth)
    save_credential_in_php_session($final_credentials);
  else
    del_credential_in_php_session();
}

$authenticatoin_msg = "<p>You are " . ($is_auth?"<span class='green'>authentificated</span>":"unknown") .
     " here, on  " . $_SERVER['HTTP_HOST'] ."</p>";

$submit_value = $is_auth?'deauth':'auth';

/*
 * HTML CODE
 */


?><html>
  <head>
    <title>Serving from <?php echo $_SERVER['HTTP_HOST']; ?></title>

    <script src="/lib/jquery/jquery.js" type="text/javascript" > </script>
    <script src="main.js" type="text/javascript" > </script>

    <style type="text/css">

input[name="auth"] {
  background: green;
}
input[name="deauth"] {
  background: red;
}
span.green {
color: green;
}

    </style>
    <?php echo $propagate_code; ?>

  </head>
  <body>

    <?php echo $authenticatoin_msg; ?>

    <?php echo $propagate_msg; ?>

    <form method="post">
      <input type="submit"
             name="<?php echo $submit_value; ?>"
             value="<?php echo $submit_value; ?>" />
    </form>

  </body>
</html>