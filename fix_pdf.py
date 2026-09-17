from pypdf import PdfReader, PdfWriter
import sys

def downgrade(input_file, output_file):
    try:
        reader = PdfReader(input_file)
        writer = PdfWriter()
        for page in reader.pages:
            writer.add_page(page)
        with open(output_file, "wb") as fp:
            writer.write(fp)
        print(f"Fixed {input_file} -> {output_file}")
    except Exception as e:
        print(f"Error for {input_file}: {e}")

downgrade('NU-LAB-01.pdf', 'NU-LAB-01_fixed.pdf')
downgrade('NU-LAB-02.pdf', 'NU-LAB-02_fixed.pdf')
