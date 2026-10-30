from pathlib import Path
import re
p=Path('theme/sab/assets/js/vertical.js');s=p.read_text().replace('vt.replaceChildren();',"vt.querySelectorAll(':scope > article').forEach(el => el.remove());")
p.write_text(s)
# Use the supplied social icon artwork; the editable option chooses the icon.
s=Path('reference/Final SAB/index.html').read_text();icons=re.findall(r'<a[^>]+aria-label="(X \(Twitter\)|LinkedIn|Instagram|Facebook)"[^>]*>(<svg[\s\S]*?</svg>)</a>',s)
keys={'X (Twitter)':'x','LinkedIn':'linkedin','Instagram':'instagram','Facebook':'facebook'}
php="<?php defined( 'ABSPATH' ) || exit;\nswitch ( $args['platform'] ?? '' ) {\n"
for label,svg in icons:php+="case '"+keys[label]+"': ?>"+svg.replace('<svg ','<svg aria-hidden="true" ')+"<?php break;\n"
php+="default: ?><svg viewBox=\"0 0 24 24\" aria-hidden=\"true\"><path d=\"M10 6h8v8h-2V9l-9 9-1-1 9-9h-5z\"/></svg><?php }\n"
Path('theme/sab/template-parts/social-icon.php').write_text(php)
