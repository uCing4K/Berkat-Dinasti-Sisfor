import json

with open('data/beranda_node.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

node = data['nodes']['10:1026']['document']

styles_summary = []

def inspect_node(n, path=""):
    name = n.get('name', '')
    ntype = n.get('type', '')
    curr = f"{path} > {name}" if path else name
    
    info = {
        'id': n.get('id'),
        'name': name,
        'type': ntype,
        'path': curr,
        'bbox': n.get('absoluteBoundingBox'),
        'cornerRadius': n.get('cornerRadius'),
        'rectangleCornerRadii': n.get('rectangleCornerRadii'),
        'strokes': n.get('strokes'),
        'strokeWeight': n.get('strokeWeight'),
        'effects': n.get('effects'),
        'fills': n.get('fills'),
        'style': n.get('style'), # typography for text
        'layoutMode': n.get('layoutMode'),
        'primaryAxisAlignItems': n.get('primaryAxisAlignItems'),
        'counterAxisAlignItems': n.get('counterAxisAlignItems'),
        'padding': (n.get('paddingTop'), n.get('paddingRight'), n.get('paddingBottom'), n.get('paddingLeft')),
        'itemSpacing': n.get('itemSpacing')
    }
    styles_summary.append(info)
    for c in n.get('children', []):
        inspect_node(c, curr)

inspect_node(node)

with open('data/styles_extracted.json', 'w', encoding='utf-8') as out:
    json.dump(styles_summary, out, indent=2)

print(f"Extracted styles for {len(styles_summary)} nodes into data/styles_extracted.json")
