import json

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = {n['id']: n for n in json.load(f)}

header_row = nodes['10:1136']
print("Header Row:", header_row['bbox'])

with open('data/beranda_node.json', 'r', encoding='utf-8') as f:
    raw = json.load(f)['nodes']['10:1026']['document']

def find_by_id(n, tid):
    if n.get('id') == tid:
        return n
    for c in n.get('children', []):
        res = find_by_id(c, tid)
        if res:
            return res
    return None

hr = find_by_id(raw, '10:1136')
for c in hr.get('children', []):
    print(f"Header cell: {c.get('name')} bbox={c.get('absoluteBoundingBox')}")

r1 = find_by_id(raw, '10:1148')
for c in r1.get('children', []):
    print(f"Row1 cell: {c.get('name')} bbox={c.get('absoluteBoundingBox')} fills={c.get('fills')}")
