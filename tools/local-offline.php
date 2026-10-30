<?php
/** Local-only HTTP isolation and IPv4 HTTP transport for macOS PHP-FPM. Never deploy this MU plugin. */
add_filter('pre_http_request', function($pre,$args,$url){
 if(wp_get_environment_type()!=='local')return $pre;
 $host=wp_parse_url($url,PHP_URL_HOST);
 $allowed=['sab.local','localhost','127.0.0.1','api.github.com','github.com','release-assets.githubusercontent.com','api.wordpress.org','downloads.wordpress.org'];
 if(!in_array($host,$allowed,true))return new WP_Error('sab_local_offline','External update checks are isolated in this local review environment. GitHub is allowed.');
 return $pre;
},10,3);
// Avoid macOS NAT64 getaddrinfo initialization after PHP-FPM forks.
// IPv4 resolution avoids that OS crash path; normal TLS checks remain enabled.
add_action('http_api_curl',function($handle){
 if(wp_get_environment_type()==='local')curl_setopt($handle,CURLOPT_IPRESOLVE,CURL_IPRESOLVE_V4);
},10,1);
