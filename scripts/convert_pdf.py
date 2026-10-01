import pymupdf
import os

pdf_path = "c:\\Users\\Dzauq Bachrul 'Ulum\\Downloads\\all of my work\\Application\\Berkat-Dinasti\\UI UX\\BERKAT DINASTI - Design System.pdf"
output_dir = "C:\\Temp\\berkat_pdf_pages"
os.makedirs(output_dir, exist_ok=True)

doc = pymupdf.open(pdf_path)
print(f"Total pages: {len(doc)}")
for i, page in enumerate(doc):
    pix = page.get_pixmap(dpi=120)
    img_path = os.path.join(output_dir, f"page_{i+1:02d}.png")
    pix.save(img_path)
    print(f"Saved page {i+1}: {img_path}")
doc.close()
print("Done!")
