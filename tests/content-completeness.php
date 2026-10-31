<?php
/** Check every imported text record, required label and translated route. */
function sab_complete($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
$seed=json_decode(file_get_contents(SAB_THEME_DATA_DIR.'seed/content.json'),true);
foreach(sab_string_fields() as $field){
 if('sab_address'===$field['name'])continue; // Optional address has never been provided.
 $source=get_field($field['name'],'sab_global',false);
 sab_complete(is_string($source)&&''!==trim($source),'Source label '.$field['name']);
 foreach(['en','ar'] as $lang)sab_complete(''!==trim(pll_translate_string($source,$lang)),$lang.' translated label '.$field['name']);
}
foreach(['en','ar'] as $lang){
 $records=get_posts(['post_type'=>['page','sab_milestone','sab_leader'],'post_status'=>'publish','posts_per_page'=>-1,'lang'=>$lang,'suppress_filters'=>false]);
 foreach($records as $post){
  sab_complete(''!==trim($post->post_title),$lang.' title '.$post->ID);
  $key=get_post_meta($post->ID,'_sab_seed_key',true);
  if(!$key)continue;
  if(preg_match('/^milestone_(\d+)_/',$key,$m)){
   $expected=$seed['items'][(int)$m[1]][$lang];
   if(''!==trim($expected['desc']))sab_complete(''!==trim($post->post_content),$lang.' milestone body '.$post->ID);
  }
  if('sab_leader'===$post->post_type)sab_complete(''!==trim($post->post_content)&&get_field('sab_pull_quote',$post->ID),$lang.' leadership body and quote '.$post->ID);
  if('page'!==$post->post_type)continue;
  $url=get_permalink($post);$response=wp_remote_get($url,['redirection'=>0,'timeout'=>20]);
  sab_complete(!is_wp_error($response)&&200===wp_remote_retrieve_response_code($response),$lang.' canonical route returns 200 without redirect: '.$url);
  $html=wp_remote_retrieve_body($response);
  sab_complete((bool)preg_match('/<html[^>]+lang="'.('ar'===$lang?'ar':'en-US').'"/',$html),$lang.' HTML language '.$url);
  foreach(('page-templates/timeline.php'===get_post_meta($post->ID,'_wp_page_template',true)?['close','hero_media_label']:['film_sound_on','film_skip','dark_label','previous','next']) as $key){
   $label=pll_translate_string((string)get_field('sab_label_'.$key,'sab_global',false),$lang);
   sab_complete(str_contains($html,esc_html($label))||str_contains($html,esc_attr($label)),$lang.' rendered '.$key.' '.$url);
  }
 }
}
echo "Content and language-route audit complete.\n";
