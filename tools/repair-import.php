<?php
// One-time correction for the first local import, before the inherited helper was fixed.
foreach(get_posts(['post_type'=>['page','sab_milestone','sab_leader'],'posts_per_page'=>-1,'post_status'=>'publish','suppress_filters'=>true,'lang'=>'']) as $p){
 if(get_post_meta($p->ID,'_sab_seed_key',true) && str_ends_with($p->post_name,'-2')) wp_update_post(['ID'=>$p->ID,'post_name'=>substr($p->post_name,0,-2)]);
}
$options=get_option('polylang');
foreach(wp_get_nav_menus(['hide_empty'=>false,'lang'=>'']) as $menu){
 $lang=pll_get_term_language($menu->term_id);
 foreach(['primary','footer'] as $loc) if(str_contains(strtolower($menu->name),$loc))$options['nav_menus'][get_stylesheet()][$loc][$lang]=$menu->term_id;
}
update_option('polylang',$options);
flush_rewrite_rules();
echo "Initial shared slugs and bilingual menu assignments corrected.\n";
