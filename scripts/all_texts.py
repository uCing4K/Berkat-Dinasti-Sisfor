import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = json.load(f)

for n in nodes:
    if n.get('type') == 'TEXT':
        st = n.get('style', {})
        chars = n.get('characters', '').replace('\n', ' ')
        fills = n.get('fills', [])
        c_str = ""
        if fills and fills[0].get('type') == 'SOLID':
            c = fills[0]['color']
            c_str = f"#{int(c['r']*255):02x}{int(c['g']*255):02x}{int(c['b']*255):02x}"
        print(f"[{st.get('fontWeight')} {st.get('fontSize')}px {c_str}] \"{chars}\" (id: {n['id']})")
