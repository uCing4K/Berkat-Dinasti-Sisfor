import json

with open('data/beranda_node.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

node = data['nodes']['10:1026']['document']

def find_nodes(n, path=""):
    name = n.get('name', '')
    ntype = n.get('type', '')
    curr = f"{path} > {name}" if path else name
    res = [(curr, n)]
    for c in n.get('children', []):
        res.extend(find_nodes(c, curr))
    return res

all_n = find_nodes(node)
print(f"Total nodes: {len(all_n)}")

# Print top level children
for c in node.get('children', []):
    print(f"Top child: {c.get('name')} [{c.get('type')}] bbox: {c.get('absoluteBoundingBox')}")
    for sc in c.get('children', []):
        print(f"  Sub: {sc.get('name')} [{sc.get('type')}] bbox: {sc.get('absoluteBoundingBox')}")
