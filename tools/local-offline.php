<?php
/** Local-only HTTP isolation and a streams transport for GitHub. Never deploy this MU plugin. */
add_filter('pre_http_request', function($pre,$args,$url){
 if(wp_get_environment_type()!=='local')return $pre;
 $host=wp_parse_url($url,PHP_URL_HOST);
 $allowed=['sab.local','localhost','127.0.0.1','api.github.com','github.com','release-assets.githubusercontent.com'];
 if(!in_array($host,$allowed,true))return new WP_Error('sab_local_offline','External update checks are isolated in this local review environment. GitHub is allowed.');
 return $pre;
},10,3);
// Avoid the macOS Local cURL worker's Objective-C fork crash; TLS verification stays enabled.
add_action('requests-requests.before_request',function(&$url,&$headers,&$data,&$type,&$options){
 if(wp_get_environment_type()==='local' && in_array(wp_parse_url($url,PHP_URL_HOST),['api.github.com','github.com','release-assets.githubusercontent.com'],true))$options['transport']=new \WpOrg\Requests\Transport\Fsockopen();
},10,5);
