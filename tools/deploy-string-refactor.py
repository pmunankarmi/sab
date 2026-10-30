from pathlib import Path
import shutil,re
root=Path('/Users/puskar/Documents/ChatGPT/SAB');wp=Path('/Users/puskar/Documents/Sites/SAB/app/public/wp-content')
plugin=wp/'plugins/sab-content'
old=(plugin/'sab-content.php').read_text();version=re.search(r'Version:\s*(\S+)',old)[1]
for name in ['options.php','string-migration.php','import.php']:shutil.copy2(root/'plugins/sab-content/includes'/name,plugin/'includes'/name)
s=(root/'plugins/sab-content/sab-content.php').read_text().replace('Version: 1.0.0','Version: '+version)
(plugin/'sab-content.php').write_text(s)
for name in ['inc/content.php','footer.php']:shutil.copy2(root/'theme/sab'/name,wp/'themes/sab'/name)
print('Installed string refactor while preserving installed release version headers.')
