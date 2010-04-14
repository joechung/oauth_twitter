<?php
require 'globals.php';
require 'oauth_helper.php';

// Fill in the next 2 variables.
$access_token='12345678-xewriiooia23AEWfsSAD23AFret5645Ddaewrewsf';
$access_token_secret='sdfasjkfKHKLkhdkfjwerFDSEWqfds243WFa24Fdfxf';
$tweet = 'Hello World!';

// POST a tweet using OAuth authentication
$retarr = post_tweet(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET,
                           $tweet, $access_token, $access_token_secret,
                           true, true);
exit(0);

/**
 * Call twitter to post a tweet
 * @param string $consumer_key obtained when you registered your app
 * @param string $consumer_secret obtained when you registered your app
 * @param string $status_message
 * @param string $access_token obtained from get_request_token
 * @param string $access_token_secret obtained from get_request_token
 * @param bool $usePost use HTTP POST instead of GET
 * @param bool $passOAuthInHeader pass OAuth credentials in HTTP header
 * @return response string or empty array on error
 */
function post_tweet($consumer_key, $consumer_secret, $status_message, $access_token, $access_token_secret, $usePost=true, $passOAuthInHeader=true)
{
  $retarr = array();  // return value
  $response = array();

  $url = 'http://api.twitter.com/1/statuses/update.json';
  $params['status'] = $status_message;
  $params['oauth_version'] = '1.0';
  $params['oauth_nonce'] = mt_rand();
  $params['oauth_timestamp'] = time();
  $params['oauth_consumer_key'] = $consumer_key;
  $params['oauth_token'] = $access_token;

  // compute hmac-sha1 signature and add it to the params list
  $params['oauth_signature_method'] = 'HMAC-SHA1';
  $params['oauth_signature'] =
      oauth_compute_hmac_sig($usePost? 'POST' : 'GET', $url, $params,
                             $consumer_secret, $access_token_secret);

  // Pass OAuth credentials in a separate header or in the query string
  if ($passOAuthInHeader) {
    $query_parameter_string = oauth_http_build_query($params, true);
    $header = build_oauth_header($params, "Twitter API");
    $headers[] = $header;
  } else {
    $query_parameter_string = oauth_http_build_query($params);
  }

  // POST or GET the request
  if ($usePost) {
    $request_url = $url;
    logit("tweet:INFO:request_url:$request_url");
    logit("tweet:INFO:post_body:$query_parameter_string");
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    $response = do_post($request_url, $query_parameter_string, 80, $headers);
  } else {
    $request_url = $url . ($query_parameter_string ?
                           ('?' . $query_parameter_string) : '' );
    logit("tweet:INFO:request_url:$request_url");
    $response = do_get($request_url, 80, $headers);
  }

  // extract successful response
  if (! empty($response)) {
    list($info, $header, $body) = $response;
    if ($body) {
      logit("tweet:INFO:response:");
      print(json_pretty_print($body));
    }
    $retarr = $response;
  }

  return $retarr;
}
?>
