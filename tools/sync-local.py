from pathlib import Path
import shutil
root=Path('/Users/puskar/Documents/ChatGPT/SAB'); wp=Path('/Users/puskar/Documents/Sites/SAB/app/public/wp-content')
for source,dest in [(root/'theme/sab',wp/'themes/sab'),(root/'plugins/sab-content',wp/'plugins/sab-content')]:shutil.copytree(source,dest,dirs_exist_ok=True)
print('SAB source synchronized to local WordPress.')
