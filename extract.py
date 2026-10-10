import json

def extract_text(node):
    texts = []
    if node.get('type') == 'TEXT':
        texts.append(node.get('characters', ''))
    for child in node.get('children', []):
        texts.extend(extract_text(child))
    return texts

with open('reset_password_node.json', 'r', encoding='utf-8') as f:
    data = json.load(f)
    
nodes = data.get('nodes', {}).get('10:834', {}).get('document', {})
all_texts = extract_text(nodes)
print("\n".join([t for t in all_texts if t.strip()]))
