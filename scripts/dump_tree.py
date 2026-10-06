import json

with open('data/beranda_node.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

node = data['nodes']['10:1026']['document']

def dump_tree(n, indent=0):
    name = n.get('name', '')
    ntype = n.get('type', '')
    chars = f" -> text: \"{n.get('characters', '')}\"" if n.get('characters') is not None else ""
    layout = f" (layout: {n.get('layoutMode')}, gap: {n.get('itemSpacing')}, pad: ({n.get('paddingTop')},{n.get('paddingRight')},{n.get('paddingBottom')},{n.get('paddingLeft')}))" if n.get('layoutMode') else ""
    fills = ""
    if 'fills' in n and n['fills']:
        f_list = []
        for fill in n['fills']:
            if fill.get('type') == 'SOLID' and 'color' in fill:
                c = fill['color']
                a = fill.get('opacity', c.get('a', 1))
                r, g, b = int(c.get('r',0)*255), int(c.get('g',0)*255), int(c.get('b',0)*255)
                f_list.append(f"#{r:02x}{g:02x}{b:02x} (a={a:.2f})")
        if f_list:
            fills = f" fills: [{', '.join(f_list)}]"
    
    print("  " * indent + f"- {name} [{ntype}] id={n.get('id')}{chars}{layout}{fills}")
    for c in n.get('children', []):
        dump_tree(c, indent + 1)

with open('data/tree_dump.txt', 'w', encoding='utf-8') as out:
    import sys
    orig = sys.stdout
    sys.stdout = out
    dump_tree(node)
    sys.stdout = orig

print("Dumped tree to data/tree_dump.txt")
