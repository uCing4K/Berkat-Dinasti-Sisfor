import json

with open('data/styles_extracted.json', 'r', encoding='utf-8') as f:
    nodes = {n['id']: n for n in json.load(f)}

left = nodes['10:1245']
right = nodes['10:1295']
row = nodes['10:1244']
print("Row:", row['bbox'])
print("Left Widget:", left['bbox'])
print("Right Widget:", right['bbox'])
