import json

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = {n['id']: n for n in json.load(f)}

for cid in ['10:1048', '10:1066', '10:1084', '10:1102']:
    c = nodes[cid]
    print(f"Card {c['name']}: bbox={c['bbox']} padding={c['padding']} cornerRadius={c['cornerRadius']}")

for bid in ['10:1065', '10:1083', '10:1101', '10:1119']:
    b = nodes[bid]
    print(f"Bar {b['name']}: id={b['id']} bbox={b['bbox']} cornerRadius={b.get('cornerRadius')} rectRadii={b.get('rectangleCornerRadii')}")
