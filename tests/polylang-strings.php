<?php
function strings_check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
strings_check((bool)get_option('sab_polylang_strings_migrated_v1'),'Migration completed');
$before=json_decode(file_get_contents('/private/tmp/sab-legacy-strings-before.json'),true);
$count=0;
foreach(sab_string_fields() as $field){
 $source=(string)get_field($field['name'],'sab_global',false);
 foreach(['en','ar'] as $lang){
  $expected=$before[$lang][$field['name']]??null;
  if(null===$expected||''===(string)$expected)continue;
  strings_check(pll_translate_string($source,$lang)===(string)$expected,'Preserved '.$lang.' '.$field['name']);$count++;
  strings_check((string)get_field($field['name'],'sab_'.$lang,false)===(string)$expected,'Older theme compatibility '.$lang.' '.$field['name']);
 }
}
strings_check(count((array)get_field('sab_logo_variants','sab_global'))===2,'Logo variants retained on shared options page');
$schema=acf_get_options_pages();strings_check(!isset($schema['sab-options-en'])&&!isset($schema['sab-options-ar']),'Separate EN/AR option pages removed');
$source=(string)get_field('sab_label_close','sab_global',false);$language=PLL()->model->get_language('ar');$original=new PLL_MO();$original->import_from_db($language);
try{
 $changed=new PLL_MO();$changed->import_from_db($language);$changed->add_entry(new Translation_Entry(['singular'=>$source,'translations'=>['SAB-Polylang-verification']]));$changed->export_to_db($language);
 foreach(['/ar/home/','/ar/timeline/'] as $path){$r=wp_remote_get(home_url($path),['timeout'=>25]);strings_check(!is_wp_error($r)&&200===wp_remote_retrieve_response_code($r)&&str_contains(wp_remote_retrieve_body($r),'SAB-Polylang-verification'),'Polylang edit reaches '.$path);}
}finally{$original->export_to_db($language);}
strings_check(false===sab_migrate_option_strings(),'Migration is idempotent and does not overwrite later translations');
foreach(['/en/home/','/ar/home/','/en/timeline/','/ar/timeline/'] as $path){$r=wp_remote_get(home_url($path),['timeout'=>25]);strings_check(!is_wp_error($r)&&200===wp_remote_retrieve_response_code($r),'HTTP 200 '.$path);}
echo 'Verified '.$count." migrated translation values. Test translation restored.\n";
