import json

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = json.load(f)

fonts = {}
for n in nodes:
    st = n.get('style')
    if st:
        key = f"{st.get('fontFamily')} {st.get('fontWeight')} {st.get('fontSize')}px line:{st.get('lineHeightPx')}"
        if key not in fonts:
            fonts[key] = []
        fonts[key].append(n['name'] + ' -> ' + (n.get('characters', '')[:30] if n.get('characters') else ''))

for k, v in fonts.items():
    print(k, f"({len(v)} occurrences)")
    for sample in v[:3]:
        print("  ", sample)
