

/* list of urls to propagate to...
 *
 */

urls = ["http://127.0.0.1/multidomain",
        "http://localhost/multidomain"];



function propagate(url, session_id, origin) {
  var ajax = {
    type: "POST",
    dataType: 'text',
    cache: false,
    data: {"session_id": session_id,
           "origin": origin},
    url: url + "/auth-with-session-id.php",
    xhrFields: {
      withCredentials: true
    }
  };
  return $.ajax(ajax);
}


function propagate_authentication_status(origin) {
  
  var res = $.Deferred();
  get_session_id().then(function(session_id) {
      var deferreds = [];
      for(var i=0; i < urls.length; i++) {
        url = urls[i];
        deferreds.push(propagate(url, session_id, origin));
      }
      return $.when(deferreds).done(function() {
          res.resolve();
        });
    });
  return res;
}
