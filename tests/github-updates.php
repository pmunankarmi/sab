<?php
/** Deterministic update-response checks; never installs or downloads executable code. */
function github_check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
$fixture=['tag_name'=>'v1.0.999999','draft'=>false,'prerelease'=>false,'published_at'=>'2026-10-30T00:00:00Z','assets'=>[
 ['name'=>'sab-theme.zip','browser_download_url'=>SAB_GitHub_Updates::REPO.'/releases/download/v1.0.999999/sab-theme.zip'],
 ['name'=>'sab-content.zip','browser_download_url'=>SAB_GitHub_Updates::REPO.'/releases/download/v1.0.999999/sab-content.zip']]];
$mock=function($pre,$args,$url)use(&$fixture){if(SAB_GitHub_Updates::API!==$url)return $pre;return ['headers'=>[],'body'=>wp_json_encode($fixture),'response'=>['code'=>200,'message'=>'OK'],'cookies'=>[]];};
$previous=get_site_transient(SAB_GitHub_Updates::CACHE);$last=get_site_option('sab_github_last_check');
add_filter('pre_http_request',$mock,99,3);
try{
 delete_site_transient(SAB_GitHub_Updates::CACHE);
 $theme=SAB_GitHub_Updates::theme_update(false,['UpdateURI'=>SAB_GitHub_Updates::REPO],'sab',[]);
 github_check($theme['version']==='1.0.999999'&&str_ends_with($theme['package'],'sab-theme.zip'),'Native theme update returns versioned theme ZIP');
 $plugin=SAB_GitHub_Updates::plugin_update(false,['UpdateURI'=>SAB_GitHub_Updates::REPO.'/sab-content'],'sab-content/sab-content.php',[]);
 github_check($plugin['version']==='1.0.999999'&&str_ends_with($plugin['package'],'sab-content.zip'),'Native plugin update returns versioned plugin ZIP');
 github_check(false===SAB_GitHub_Updates::theme_update(false,['UpdateURI'=>'https://example.org'],'unrelated',[]),'Unrelated themes are not modified');
 github_check(false===SAB_GitHub_Updates::plugin_update(false,['UpdateURI'=>'https://example.org'],'unrelated/plugin.php',[]),'Unrelated plugins are not modified');
 $fixture['assets'][0]['browser_download_url']='https://example.org/untrusted.zip';delete_site_transient(SAB_GitHub_Updates::CACHE);
 github_check(isset(SAB_GitHub_Updates::release()['error']),'Reject package URLs outside the specified repository');
 $fixture['assets'][0]['browser_download_url']=SAB_GitHub_Updates::REPO.'/releases/download/v1.0.999999/sab-theme.zip';$fixture['prerelease']=true;delete_site_transient(SAB_GitHub_Updates::CACHE);
 github_check(isset(SAB_GitHub_Updates::release()['error']),'Prereleases are not offered as stable updates');
 $fixture['prerelease']=false;$fixture['tag_name']='invalid';delete_site_transient(SAB_GitHub_Updates::CACHE);
 github_check(isset(SAB_GitHub_Updates::release()['error']),'Invalid release versions do not create updates');
}finally{
 remove_filter('pre_http_request',$mock,99);delete_site_transient(SAB_GitHub_Updates::CACHE);
 if(false!==$previous)set_site_transient(SAB_GitHub_Updates::CACHE,$previous,HOUR_IN_SECONDS);
 update_site_option('sab_github_last_check',$last);
}
echo "GitHub update tests complete; fixtures removed.\n";
