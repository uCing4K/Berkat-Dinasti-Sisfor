import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = json.load(f)

with open('data/components_summary.txt', 'w', encoding='utf-8') as out:
    for n in nodes:
        name = n['name']
        cr = n.get('cornerRadius') or n.get('rectangleCornerRadii')
        eff = n.get('effects')
        bb = n.get('bbox')
        strokes = n.get('strokes')
        style = n.get('style')
        fills = n.get('fills')
        
        # Check if it has something noteworthy
        if style or cr or eff or strokes or any(k in name for k in ['Card', 'Header', 'Aside', 'Button', 'Table', 'Row', 'Widget', 'Badge', 'Overlay', 'Banner', 'Input', 'Batch', 'Calendar']):
            out.write(f"[{n['type']}] {name} (id: {n['id']})\n")
            out.write(f"  path: {n['path']}\n")
            if bb:
                out.write(f"  bbox: w={bb.get('width'):.1f}, h={bb.get('height'):.1f}, x={bb.get('x'):.1f}, y={bb.get('y'):.1f}\n")
            if cr:
                out.write(f"  cornerRadius: {cr}\n")
            if eff:
                for e in eff:
                    if e.get('visible', True):
                        out.write(f"  effect: {e.get('type')} color={e.get('color')} offset={e.get('offset')} radius={e.get('radius')}\n")
            if strokes:
                for s in strokes:
                    out.write(f"  stroke: {s.get('type')} color={s.get('color')} weight={n.get('strokeWeight')}\n")
            if style:
                out.write(f"  font: family={style.get('fontFamily')} weight={style.get('fontWeight')} size={style.get('fontSize')} lineGap={style.get('lineHeightPx')} align={style.get('textAlignHorizontal')}\n")
            if fills:
                f_list = []
                for fill in fills:
                    if fill.get('type') == 'SOLID' and 'color' in fill:
                        c = fill['color']
                        a = fill.get('opacity', c.get('a', 1))
                        r, g, b = int(c.get('r',0)*255), int(c.get('g',0)*255), int(c.get('b',0)*255)
                        f_list.append(f"#{r:02x}{g:02x}{b:02x} (a={a:.2f})")
                if f_list:
                    out.write(f"  fills: {', '.join(f_list)}\n")
            out.write("\n")

print("Generated data/components_summary.txt")
