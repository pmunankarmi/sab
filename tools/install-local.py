from pathlib import Path
import shutil,subprocess
workspace=Path('/Users/puskar/Documents/ChatGPT/SAB')
wp=Path('/Users/puskar/Documents/Sites/SAB/app/public')
for source,dest in [(workspace/'theme/sab',wp/'wp-content/themes/sab'),(workspace/'plugins/sab-content',wp/'wp-content/plugins/sab-content')]:shutil.copytree(source,dest,dirs_exist_ok=True)
prior=Path('/Users/puskar/Documents/Sites/Tayseer/app/public/wp-content/plugins')
for name in ['advanced-custom-fields-pro','polylang']:
 dest=wp/'wp-content/plugins'/name
 if not dest.exists():shutil.copytree(prior/name,dest)
for args in [['plugin','activate','advanced-custom-fields-pro','polylang','sab-content'],['theme','activate','sab'],['sab','setup'],['sab','import']]:
 subprocess.run([str(workspace/'tools/local-wp'),*args],check=True)
