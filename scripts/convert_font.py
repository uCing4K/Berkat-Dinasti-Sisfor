from fontTools.ttLib import TTFont
import os

src = r"c:\Users\Dzauq Bachrul 'Ulum\Downloads\all of my work\Application\Berkat-Dinasti\UI UX\Inter\Inter-VariableFont_opsz,wght.ttf"
dst = r"c:\Users\Dzauq Bachrul 'Ulum\Downloads\all of my work\Application\Berkat-Dinasti\UI UX\Inter-Variable.woff2"

font = TTFont(src)
font.flavor = "woff2"
font.save(dst)
print(f"Saved: {dst}")
print(f"Size: {os.path.getsize(dst) / 1024:.1f} KB")
