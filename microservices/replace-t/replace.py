import os
import argparse
from PyPDF2 import PdfFileReader, PdfFileWriter, PdfReader, PdfWriter
from PyPDF2.generic import DecodedStreamObject, EncodedStreamObject, NameObject


def replace_text(content, replacements = dict()):
    lines = content.splitlines()

    result = ""
    in_text = False

    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False

        elif in_text:
            cmd = line[-2:]
            if cmd.lower() == 'tj':
                replaced_line = line
                # print(replaced_line)
                # print('----------')
                # print(replacements.items())
                for k, v in replacements.items():
                    replaced_line = replaced_line.replace(k, v)
                result += replaced_line + "\n"
            else:
                result += line + "\n"
            continue

        result += line + "\n"
    return result


def process_data(object, replacements):
    data = object.get_data()
    decoded_data = data.decode('utf-8')

    replaced_data = replace_text(decoded_data, replacements)

    encoded_data = replaced_data.encode('utf-8')
    if object.decoded_self is not None:
        object.decoded_self.set_data(encoded_data)
    else:
        object.set_data(encoded_data)


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("-i", "--input", required=True, help="path to PDF document")
    args = vars(ap.parse_args())

    in_file = args["input"]
    filename_base = in_file.replace(os.path.splitext(in_file)[1], "")

    # Provide replacements list that you need here
    replacements = { '[HonourstatNL]': 'Geslaagd ', '[HonourstatEN]': 'Gradated', '[FirstName]': 'Reymon',
                     '[LastName]': 'Zakhary', '[Birthplace]': 'Cairo Egypt', '[BirthdateNL]': '12 Mai 2022',
                     '[BirthdateEN]': '12 Mei 2200', '[GradDateNL]': '20-12-2020',
                     '[GradDateEN]': '12 OKt 3000', '[Diploma NL]': 'This is je new diploma', '[MiddleName]': '',
                     '[Diploma EN]': 'This is a we can go',
                     '[!fullname]': 'Reymon Zakhary',
                     '[HonourStat]': 'Gradated', '[BirthdateN]': '12-12-2112', '[BirthdateE]': '12 Mai 2020'}

    pdf = PdfReader(in_file)
    writer = PdfWriter()

    for page_number in range(0, len(pdf.pages)):

        page = pdf.pages[page_number]
        contents = page.get_contents()
        # print(page)

        if isinstance(contents, DecodedStreamObject) or isinstance(contents, EncodedStreamObject):
            process_data(contents, replacements)
        elif len(contents) > 0:
            for obj in contents:
                if isinstance(obj, DecodedStreamObject) or isinstance(obj, EncodedStreamObject):
                    streamObj = obj.getObject()
                    process_data(streamObj, replacements)

        print(contents)
        # Force content replacement
        page[NameObject("/Contents")] = contents.decoded_self
        writer.add_page(page)

    with open(filename_base + ".result.pdf", 'wb') as out_file:
        writer.write(out_file)
