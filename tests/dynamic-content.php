<?php
function dynamic_check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
$records=get_posts(['post_type'=>'sab_milestone','lang'=>'en','posts_per_page'=>1,'suppress_filters'=>false,'orderby'=>'menu_order','order'=>'ASC']);
$p=$records[0]; $original=$p->post_title;
$marker='SAB dynamic content verification '.wp_generate_password(8,false);
try{
 wp_update_post(['ID'=>$p->ID,'post_title'=>$marker]);
 foreach(['/en/home/','/en/timeline/'] as $path){$r=wp_remote_get(home_url($path),['timeout'=>20]);dynamic_check(!is_wp_error($r)&&str_contains(wp_remote_retrieve_body($r),$marker),'Native title edit appears in '.$path);}
}finally{wp_update_post(['ID'=>$p->ID,'post_title'=>$original]);}
$test_id=wp_insert_post(['post_type'=>'sab_milestone','post_status'=>'publish','post_title'=>'Temporary content count check','menu_order'=>9999]);pll_set_post_language($test_id,'en');update_field('field_sab_year',2027,$test_id);
try{
 foreach(['/en/home/','/en/timeline/'] as $path){
  $r=wp_remote_get(home_url($path),['timeout'=>20]);$html=wp_remote_retrieve_body($r);preg_match('/window.SAB=(\{[^\r\n]*\});/',$html,$m);$config=json_decode($m[1]??'',true);
  dynamic_check(isset($config['items'])&&count($config['items'])===37,'New milestone updates count in '.$path);
  dynamic_check($config['items'][36]['year']===2027,'Native Order places new milestone last in '.$path);
  dynamic_check(!str_contains($html,'Fatal error'),'Milestone without image or video renders safely '.$path);
 }
}finally{wp_delete_post($test_id,true);}
echo "Dynamic content checks complete; all temporary changes restored.\n";
