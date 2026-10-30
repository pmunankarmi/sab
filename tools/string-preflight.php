<?php
$schema=json_decode(file_get_contents(SAB_CONTENT_DIR.'seed/option-fields.json'),true);
$en=[];$ar=[];$collisions=[];
foreach($schema as $f){if(!in_array($f['type'],['text','textarea'],true))continue;$name=$f['name'];$e=get_option('sab_en_'.$name,null);$a=get_option('sab_ar_'.$name,null);$en[$name]=$e;$ar[$name]=$a;if(empty($e)&&!empty($a))echo "REVIEW missing English source for $name\n";if(!empty($e)&&isset($collisions[$e])&&$collisions[$e]!==$a)echo "REVIEW conflicting translations for $name\n";if(!empty($e))$collisions[$e]=$a;}
echo 'Legacy text fields: '.count($en)."\n";
file_put_contents('/private/tmp/sab-legacy-strings-before.json',wp_json_encode(['en'=>$en,'ar'=>$ar],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
echo "Legacy values snapshot saved before migration.\n";
