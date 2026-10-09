from pypdf import PdfReader
import sys

pdf_path = sys.argv[1]

reader = PdfReader(pdf_path)

text = ""

for page in reader.pages:
    text += page.extract_text() or ""

print("PDF TEXT:")
print(text)