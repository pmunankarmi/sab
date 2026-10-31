from pathlib import Path
import zipfile
root=Path(__file__).resolve().parent.parent
out=root/'dist';out.mkdir(exist_ok=True)
for source,name in [(root/'theme/sab','sab-theme.zip'),(root/'plugins/sab-content','sab-content.zip')]:
 with zipfile.ZipFile(out/name,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as archive:
  for p in sorted(source.rglob('*')):
   if p.is_file() and '.DS_Store' not in p.parts and 'univers-src' not in p.parts:archive.write(p,Path(source.name)/p.relative_to(source))
 print(name,round((out/name).stat().st_size/1024/1024,2),'MB')
with zipfile.ZipFile(out/'sab-wordpress-delivery.zip','w',zipfile.ZIP_DEFLATED) as archive:
 for name in ['sab-theme.zip']:archive.write(out/name,name)
 for p in (root/'docs').glob('*.md'):archive.write(p,'docs/'+p.name)
print('sab-wordpress-delivery.zip ready')
