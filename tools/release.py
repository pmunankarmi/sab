"""Stamp both packages with one increasing version and produce native WordPress ZIPs."""
from pathlib import Path
import json,re,subprocess,sys
root=Path(__file__).resolve().parent.parent
run=int(sys.argv[1]);base=json.loads((root/'release-version.json').read_text())
version=f"{int(base['major'])}.{int(base['minor'])}.{run}"
for relative in ['theme/sab/style.css','plugins/sab-content/sab-content.php']:
 p=root/relative;s=p.read_text()
 s,count=re.subn(r'(?m)^(\s*(?:\*\s*)?Version:\s*)[^\r\n]+',lambda m:m[1]+version,s,count=1)
 if count!=1:raise RuntimeError('Missing version header: '+relative)
 p.write_text(s)
subprocess.run([sys.executable,str(root/'tools/package.py')],check=True)
print(version)
if len(sys.argv)>2:
 with Path(sys.argv[2]).open('a') as out:out.write('version='+version+'\n')
