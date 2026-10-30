<?php
wp_set_current_user(get_user_by('login','sabadmin')->ID);
delete_site_transient(SAB_GitHub_Updates::CACHE);
$release=SAB_GitHub_Updates::release();
if(isset($release['error']))throw new RuntimeException($release['error']);
foreach(['update_themes','update_plugins'] as $key){$value=get_site_transient($key);if(is_object($value)){$value->last_checked=0;set_site_transient($key,$value);}}
wp_update_themes();wp_update_plugins();
$themes=get_site_transient('update_themes');$plugins=get_site_transient('update_plugins');
if(empty($themes->response['sab']))throw new RuntimeException('Native theme update notice missing.');
if(empty($plugins->response['sab-content/sab-content.php']))throw new RuntimeException('Native content-plugin update notice missing.');
echo 'GitHub release '.$release['version']." detected.\nNative theme update available.\nNative content-plugin update available.\nNo update installed.\n";
