const fs = require('fs');
const vm = require('vm');
const source = fs.readFileSync('reference/Final SAB/index.html','utf8');
const vertical = fs.readFileSync('reference/Final SAB/vertical.html','utf8');
function expr(s, pattern) { const match=s.match(pattern); if(!match)throw Error('Missing source expression'); return vm.runInNewContext('('+match[1]+')',Object.create(null),{timeout:1000}); }
const items=expr(source,/const ITEMS = (\[[\s\S]*?\n\]);/);
const translations=expr(source,/const TRANSLATIONS = (\{[\s\S]*?\n\});/);
const vt=expr(vertical,/const TR = (\{[\s\S]*?\n\});/);
for(const lang of ['en','ar']) Object.assign(translations[lang],{v_title:vt[lang].v_title,v_sub:vt[lang].v_sub,nav_cinematic:vt[lang].nav_cinematic});
const extra={en:{play_century:'Play the century',pause_century:'Pause the century',previous:'Previous milestone',next:'Next milestone',close:'Close',timeline_nav:'Timeline navigation',toggle_navigation:'Toggle navigation',hero_media_label:'SAB 100 years',film_label:'SAB centenary film',skip_content:'Skip to content',leadership_title:'Leadership',language_name:'English'},ar:{play_century:'تشغيل المئوية',pause_century:'إيقاف المئوية مؤقتًا',previous:'المحطة السابقة',next:'المحطة التالية',close:'إغلاق',timeline_nav:'التنقل بين المحطات',toggle_navigation:'تبديل القائمة',hero_media_label:'الأول مئة عام',film_label:'فيلم مئوية الأول',skip_content:'انتقل إلى المحتوى',leadership_title:'القيادة',language_name:'العربية'}};
for(const lang of ['en','ar'])Object.assign(translations[lang],extra[lang]);
fs.writeFileSync('plugins/sab-content/seed/content.json',JSON.stringify({items,translations},null,2));
console.log(`Extracted ${items.length} bilingual milestones and editable interface copy.`);
