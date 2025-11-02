from PyPDF2 import PdfFileReader, PdfFileWriter, PdfReader, PdfWriter
from PyPDF2._utils import b_
from PyPDF2.generic import TextStringObject, NameObject, ContentStream, read_string_from_stream


source = PdfReader('./Diploma_SBE_2911_fontsembedded_0.pdf', "rb")
output = PdfWriter()

find = "[BirthdateN]"
change_to = "BITH a diploma object"

for page in range(len(source.pages)):

    # Get the current page and it's contents
    page = source.pages[page]
    content_object = page["/Contents"].get_object()
    content = ContentStream(content_object, source)
    to_replace = []
    for operands, operator in content.operations:
        # print(operator, operands)
        if operator == b_("BDC"):
            text = operands[1]
            # print(text)
            # if text.startswith('[FirstName]'):
            # print(operands[0])
            # operands[0] = TextStringObject('hallo')
            # print(operands[0])
        longText = ''
        if operator == b_("TJ"):
            text = operands[0]
            # print(text)
            for index, line in enumerate(text):
                if isinstance(line, str):
                    if "[" in line and ']' in line:
                        to_replace.append({"search": [{"index": index, "replace": line}]})
                        continue
                    elif '[' in line:
                        # print(line)
                        search = []
                        for p in range(0,8):
                            i = index + p
                            if i < len(text):
                                if isinstance(text[i], str):
                                    if ']' in text[i]:
                                        search.append({"index": i, "replace": text[i]})
                                    else:
                                        search.append({"index": i, "replace": text[i]})
                                else:
                                    search.append({"index": i, "replace": text[i]})

                        # print(to_replace)
                        to_replace.append({"search": search})
                    else:
                        continue

                if len(to_replace) > 0:
                    for replace in to_replace:
                        # print(replace)
                        txt = ''
                        replacer = replace['search']
                        for arr in replace['search']:
                            if isinstance(arr['replace'], str):
                                txt += arr['replace']
                            else:
                                continue
                        if find in txt:

                            for (i,arr) in enumerate(replacer):
                                # print(i,arr, replacer)
                                if isinstance(arr['replace'], str):
                                    # print(operands[0], i, change_to)
                                    if i == 0:
                                        print(i)
                                        if int(arr['index']) < len(operands[0]) and operands[0][int(arr['index'])] == arr['replace']:
                                            print(operands[0][arr['index']], change_to)
                                            operands[0][arr['index']] = TextStringObject(change_to)
                                            continue
                                    else:
                                        if int(arr['index']) < len(operands[0]):
                                            # print(operands[0][arr['index']])
                                            operands[0][arr['index']] = TextStringObject()
                                            continue

                            break


                # operands[0][2] = TextStringObject(' **** Namens de Universiteit Utricht **** ')

            # print(operands[0])

            # for index, line in enumerate(text):
            #     print(index, line)
            #     if isinstance(line, str):
            #         if line.startswith('[FirstName]'):
            #             operands[0][index] = TextStringObject('Reymon')
            #         longText += line
            # if line.startswith('[Diploma N'):
            #     operands[0][index] = TextStringObject('this is a new text the ')
            # print(text[0])
            # if text.startswith('[FirstName]'):
            #     print(operands[0])
            #     replace = text.replace('[FirstName]', 'Reymon')
            #     operands[0] = TextStringObject(replace)
            #     print(operands[0])

    page[NameObject('/Contents')] = content
    output.add_page(page)

# Write the stream
outputStream = open("output.pdf", "wb")
output.write(outputStream)
outputStream.close()

#
# from PyPDF2 import PdfFileReader, PdfFileWriter
# from PyPDF2._utils import b_
# from PyPDF2.generic import TextStringObject, NameObject, ContentStream
#
# # watermark to remove
# wm_text = "GradDateNL"
# # replacing the watermark with nothing
# replace_with = "Hello Hello"
#
# # Load PDF into pyPDF
# source = PdfFileReader("Diploma_FASoS_1501_T1.pdf", strict=False)
# output = PdfFileWriter()
#
# # Iterating through each page
# for page in range(source.getNumPages()):
#     # Current Page
#     page = source.getPage(page)
#     content_object = page["/Contents"].getObject()
#     content = ContentStream(content_object, source)
#
#     # Iterating over all pdf elements
#     for operands, operator in content.operations:
#         print(operator)
#         if operator == b_("TJ"):
#             text = operands[0][0]
#             print(text.startswith(wm_text))
#             if isinstance(text, TextStringObject) and text.startswith(wm_text):
#                 operands[0] = TextStringObject(replace_with)
#
#     page.__setitem__(NameObject("/Contents"), content)
#
#     output.addPage(page)
#
# outputStream = open("output2.pdf", "wb")
# output.write(outputStream)
