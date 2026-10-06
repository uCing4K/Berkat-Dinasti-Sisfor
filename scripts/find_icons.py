import json

with open('data/beranda_node.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

node = data['nodes']['10:1026']['document']

icons = []

def find_icons(n, path=""):
    name = n.get('name', '')
    ntype = n.get('type', '')
    curr = f"{path} > {name}" if path else name
    if 'Icon' in name or ntype == 'VECTOR':
        icons.append((n.get('id'), name, curr))
    for c in n.get('children', []):
        find_icons(c, curr)

find_icons(node)
print(f"Total icons found: {len(icons)}")
for i, name, path in icons:
    print(f"{i} : {name} ({path})")
