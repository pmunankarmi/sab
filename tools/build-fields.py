from pathlib import Path
import json
root=Path('plugins/sab-content')
def f(name,label,typ='text',**kw):return dict(key='field_'+name,name=name,label=label,type=typ,**kw)
def image(name,label,**kw):return f(name,label,'image',return_format='id',preview_size='medium',library='all',**kw)
def group(key,title,fields,param,value):
 d=dict(key='group_'+key,title=title,fields=fields,location=[[dict(param=param,operator='==',value=value)]],active=True,style='default',position='normal',show_in_rest=False)
 (root/'acf-json'/(d['key']+'.json')).write_text(json.dumps(d,indent=2))
group('sab_brand','Branding',[f('sab_brand_tab','Branding','tab'),image('sab_primary_logo','Primary logo',instructions='Uses the same native setting as Appearance → Customize → Site Identity. Changes and removal work in both locations.'),image('sab_dark_logo','Logo for dark backgrounds'),image('sab_emblem','Timeline emblem'),f('sab_contact_tab','Shared contact details','tab'),f('sab_phone','Phone'),f('sab_email','Email','email'),f('sab_website','Website','url'),f('sab_map','Map URL','url'),f('sab_social_tab','Social media','tab'),f('sab_social','Social links','repeater',layout='table',button_label='Add social link',sub_fields=[f('sab_social_platform','Platform','select',choices={'x':'X','linkedin':'LinkedIn','instagram':'Instagram','facebook':'Facebook','youtube':'YouTube','other':'Other'},return_format='value'),f('sab_social_label','Accessible label',required=1),f('sab_social_url','URL','url',required=1)])],'options_page','sab-options')
group('sab_milestone','Milestone details',[f('sab_year','Year','number',required=1,min=1,max=9999),f('sab_video','Milestone video','file',return_format='id',mime_types='mp4,webm'),image('sab_mask','Video mask'),f('sab_cutout','Cutout image','true_false',ui=1),f('sab_finale','Full-width finale treatment','true_false',ui=1),f('sab_layout','Cinematic layout','select',choices={k:k.replace('layout-','').replace('-',' ').title() for k in ['layout-stack-left','layout-pair-left','layout-stack-center','layout-pair-right','layout-stack-right']},allow_null=1,instructions='Leave empty for automatic layout. Use native Order to arrange milestones, including milestones in the same year.')],'post_type','sab_milestone')
group('sab_leader','Leadership details',[f('sab_pull_quote','Pull quote','textarea',rows=3),f('sab_role','Role')],'post_type','sab_leader')
page_fields=[f('sab_hero_title','Formatted hero heading','textarea',instructions='Optional formatted heading. Supports em, strong and br. Native page title remains the document title.'),f('sab_hero_video','Hero video','file',return_format='id',mime_types='mp4,webm'),image('sab_hero_poster','Hero poster'),f('sab_film','Centenary film','file',return_format='id',mime_types='mp4,webm'),image('sab_film_poster','Film poster'),f('sab_intro_title','Introduction heading','textarea'),f('sab_outro_title','Closing heading','textarea'),f('sab_outro_body','Closing text','textarea')]
for template in ['front-page.php','page-templates/timeline.php']:
 key='sab_home' if template=='front-page.php' else 'sab_timeline'
 group(key,'SAB page content',page_fields,'page_type' if template=='front-page.php' else 'page_template','front_page' if template=='front-page.php' else template)
labels=json.loads((root/'seed/content.json').read_text())['translations']['en']
exclude={'hero_title','intro_title_1','intro_body_1','outro_title','outro_body','v_title','v_sub','nav_vertical','nav_leadership','footer_disclaimer','footer_terms','footer_privacy','outro_meta_2','footer_rights','footer_entity'}
exclude.update(k for k in labels if k.startswith(('chair_','ceo_','leader_','intro_title_2','intro_body_2')))
fields=[f('sab_local_brand','Language-specific branding','tab'),image('sab_language_logo','Logo override for this language'),image('sab_language_dark_logo','Dark-background logo override'),image('sab_footer_logo','Footer logo'),f('sab_local_contact','Contact and footer','tab'),f('sab_address','Address','textarea'),f('sab_contact_label','Contact accessibility label'),f('sab_established','Establishment line'),f('sab_copyright','Copyright text'),f('sab_legal_text','Regulatory text','textarea'),f('sab_website_label','Website link label'),f('sab_ui_tab','Interface and accessibility labels','tab')]
for k,v in labels.items():
 if k not in exclude:fields.append(f('sab_label_'+k,k.replace('_',' ').title(),'textarea' if len(v)>100 else 'text'))
(root/'seed/option-fields.json').write_text(json.dumps(fields,indent=2))
print('Generated ACF field definitions and bilingual option schema.')
