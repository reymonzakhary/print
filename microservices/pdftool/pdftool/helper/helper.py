from boto3 import resource, client
from flask import Flask, request, jsonify, Response, send_file

from os import environ, path
from dotenv import load_dotenv
import math
import numpy as np
import re

basedir = path.abspath(path.dirname(__file__))


def keyshift(dictionary, key, i):
    keys = list(dictionary.keys())
    if key in dictionary and len(keys) > 2:
        key_index = keys.index(key)
        # newly added to the fnc
        if key_index - i >= len(keys):
            # print('@@@@', key_index, dictionary.get(len(keys) - 1))
            return dictionary.get(len(keys) - 1)
        # end newly added
        key = keys[key_index - i]
        return dictionary.get(key)
    else:
        return False


def chunck_str_into_group_of_n(str, n):
    return [str[i:i + n] for i in range(0, len(str), n)]


def replace_cmap(line, font_object):
    cmap = font_object.get('/ToUnicode')
    cmap_replaced_line = line
    if cmap is None:
        return line

    pattern = r"\\[0-9]{3}"
    match = re.finditer(pattern, line)

    cmap = cmap.get_object().get_data().decode('utf-8').splitlines()
    for m in match:
        octal_char_code = str(m.group().replace('\\', ''))
        char_unicode = hex(int(octal_char_code, 8)).replace('0x', '').upper()
        str_from_cmap = ''
        for map_obj in cmap:
            if char_unicode in map_obj[0:4]:
                chars_value = map_obj[4:].strip().replace('<', '').replace('>', '')
                chars = chunck_str_into_group_of_n(chars_value, 4)
                for char in chars:
                    str_from_cmap += chr(int(str(char), 16))

        cmap_replaced_line = cmap_replaced_line.replace(m.group(), str_from_cmap)

    return cmap_replaced_line


def replace_text(content, replacements=dict(), page_resources={}, media_box=[]):
    text_map = {}

    result = ""
    in_text = False

    lines = content.splitlines()
    text_key = ''
    text_meta_data = {}
    last_tm = ''
    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False

        if in_text:

            if 'Tf' in line:
                text_meta_data.update({'font_resource': line.split(' ')[0]})
                text_meta_data.update({'font_size': line.split(' ')[1]})

            if 'Tm' in line:
                l = line.split(' ')
                x = l[-3]
                y = l[-2]
                text_key = f"{x},{y}"
                text_meta_data.update({'Tm': line})
                if line[0] is not None and int(round(float(l[0]))) != 1:
                    text_meta_data.update({'font_size': l[0]})
                last_tm = line

            if 'Tj' in line:
                text_meta_data['line'] = line
                # check if operator is empty
                start = line.find('(')
                end = line.find(')Tj')
                text = line[start + 1: end]
                text = text.replace(' ', '')
                if len(text) != 0 and last_tm != '':
                    text_map.update({text_key: text_meta_data})
                    last_tm = ''
                text_meta_data = {}

            if 'TJ' in line:
                text_meta_data['line'] = line
                # check if operator is empty 
                if len(line[1:-3].replace(' ', '')) != 0:
                    text_map.update({text_key: text_meta_data})
                text_meta_data = {}
    # find and replace the text with placement
    result = ''
    index = 0
    tm_list = {}
    text_block = ''
    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False
        if in_text:
            text_key = ''
            if 'Tm' in line:
                index = index + 1
                tm_list.update({index: line})
                l = line.split(' ')
                x = l[-3]
                y = l[-2]
                text_key = f"{x},{y}"
                text_object = text_map.get(text_key)

                if text_object is not None and text_object.get('Tm') is not None:
                    text_block += text_object.get('Tm') + '\n'
                else:
                    text_block += line + '\n'

            elif 'Tj' in line or 'TJ' in line:
                for k, v in replacements.items():
                    if k in line:
                        if v is None or v == 'none' or v == "None": v = ''  # in case the value is set to None type or a none string
                        ## have to be check if it will produce issue with spaces
                        if isinstance(v, str) and len(v) == 0:
                            new_k = " "+k
                            line = line.replace(new_k, v)
                        v = str(v)
                        line = line.replace(k, v)
                        overflow = False
                        font_info = page_resources['/Font']
                        text_key = str(tm_list.get(index).split(' ')[-3]) + ',' + str(tm_list[index].split(' ')[-2])
                        meta = text_map.get(text_key)
                        font_resource = meta.get('font_resource')
                        key_string_width = 0
                        value_string_width = 0
                        width_diff = 0
                        if font_resource is not None:
                            font = font_info[font_resource]
                            font_size = meta.get('font_size')
                            if font.get('/Widths') is not None:
                                key_string_width = calculate_string_width(font['/Widths'], k, font_size)
                                value_string_width = calculate_string_width(font['/Widths'], v, font_size)
                                width_diff = value_string_width - key_string_width
                        else:
                            font_resource = None
                            i = 1
                            while font_resource is None:
                                previous_text_object = keyshift(text_map, text_key, i)
                                font_resource = previous_text_object.get('font_resource')
                                font_size = previous_text_object.get('font_size')
                                i = i + 1
                            font = font_info[font_resource]
                            # font_size = meta.get('font_size')
                            if font.get('/Widths') is not None:
                                key_string_width = calculate_string_width(font['/Widths'], k, font_size)
                                value_string_width = calculate_string_width(font['/Widths'], v, font_size)
                                width_diff = value_string_width - key_string_width
                        keys = list(text_map.keys())
                        for i in range(0, len(keys)):
                            key = keys[i]
                            y = str(tm_list[index].split(' ')[-2])
                            x = str(tm_list[index].split(' ')[-3])
                            if key.endswith(y):
                                if k in text_map.get(key).get('line') and i != len(keys) - 1:
                                    next_key = keys[i + 1]
                                    text_object = text_map.get(key)
                                    next_text_object = text_map.get(next_key)
                                    if text_object.get('Tm') is not None and next_text_object.get('Tm') is not None:
                                        if float(text_key.split(',')[0]) <= float(next_key.split(',')[0]):
                                            # if text_object.get('Tm').split(' ')[-3] <= next_text_object.get('Tm').split(' ')[-3]:
                                            replaced_text = v #text_object.get('line')[1:-3].replace(k, v)
                                            if replaced_text == '':
                                                replaced_text += ' '
                                            text = text_object.get('line')[1:-3]
                                            # text_width = calculate_string_width(font['/Widths'], text, font_size)
                                            replaced_text_width = calculate_string_width(font['/Widths'], replaced_text,
                                                                                         font_size)
                                            text_x_offset = text_object.get('Tm').split(' ')[-3]
                                            # replaced_text_object = meta
                                            # replaced_text_object.update({'line': line})
                                            # text_map.update({text_key: replaced_text_object})
                                            next_text_x_offset = next_text_object.get('Tm').split(' ')[-3]
                                            in_the_same_line = False

                                            # print(round(float(key.split(',')[1])) , round(float(next_key.split(',')[1])), next_key )

                                            if float(key.split(',')[1]) == float(next_key.split(',')[1]):
                                                in_the_same_line = True

                                            if (replaced_text_width + round(float(text_x_offset), 3)) > round(
                                                    float(next_text_x_offset), 3) and in_the_same_line:
                                                overlap = True
                                                next_text_tm = next_text_object.get('Tm')
                                                text_tm = next_text_object.get('Tm')

                                                next_splatted = text_tm.split(' ')

                                                if v == '':
                                                    next_splatted[-3] = str(round(float(text_x_offset) - (replaced_text_width), 3))
                                                else:
                                                    next_splatted[-3] = str(round(float(text_x_offset) + replaced_text_width + calculate_string_width(font['/Widths'], ' ', font_size), 3))

                                                next_text_tm = ' '.join(next_splatted)

                                                next_text_object.update({'Tm': next_text_tm})
                                                text_map.update({key: text_object})
                text_block += line + '\n'
            else:
                if 'ET' not in line:
                    text_block += line + '\n'
        else:
            if text_block:
                result += text_block + '\n'
            text_block = ''
            result += line + '\n'

    return result


def calculate_string_width(glyph_metrics, string, font_size):
    width = 0
    final_string = string
    for i in range(0, len(string) - 1):
        if '\\' == string[i]:
            if i + 4 < len(string):
                cmap_char = string[i: i + 4]
                final_string = string.replace(cmap_char, 'A')

    for c in final_string:
        glyph_unicode = ord(c)
        if glyph_unicode < len(glyph_metrics):
            glyph_standard_width = glyph_metrics[glyph_unicode]
        else:
            glyph_standard_width = 1
        # if font size 12 (glyph standarad width / 1000 ) * 12
        glyph_view_width = (int(float(font_size)) / 1000) * glyph_standard_width

        width = width + glyph_view_width
    return width


def calculate_text_matrix(tm, td):
    tm = tm.split(' ')
    td = td.split(' ')
    tm_index = tm.index('Tm')

    for i in range(tm_index - 1, tm_index - 7, -1):
        tm[i] = round(float(tm[i]), 3)

    for i in range(len(td) - 2, len(td) - 4, -1):
        td[i] = round(float(td[i]), 3)

    tm_mat = [
        [tm[tm_index - 6], tm[tm_index - 5], 0],
        [tm[tm_index - 4], tm[tm_index - 3], 0],
        [tm[tm_index - 2], tm[tm_index - 1], 1]
    ]

    td_mat = [
        [1, 0, 0],
        [0, 1, 0],
        [td[-3], td[-2], 1]
    ]

    tm_mat = np.array(tm_mat)
    td_mat = np.array(td_mat)

    result = np.matmul(td_mat, tm_mat)

    return f"{result[0][0]} {result[0][1]} {result[1][0]} {result[1][1]} {round(result[2][0], 3)} {round(result[2][1], 3)} Tm"


def convert_TJ_to_Tj(text):
    text = text.replace('TJ', '')
    text = ' ' + text.strip()[1:-1] + ' '
    text_array = text.split(' (')
    txt = ''
    for string_object in text_array:
        closing_parentheses_index = -1
        for i in range(len(string_object) - 1, 0, -1):
            if string_object[i] == ')':
                closing_parentheses_index = i
                break
        if len(string_object) > 0:
            txt += string_object[0: closing_parentheses_index]
    return txt


def normalize_pdf(content, page_resources={}):
    lines = content.splitlines()
    result = ''
    in_text = False
    last_tm = '1 0 0 1 0 0 Tm'
    last_tl = 0
    text_block = 'BT\n'
    text = ''
    last_font_resource = ''
    separated_text = False
    prepend_space = False
    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            prepend_space = False
            in_text = False

        if in_text:
            if 'Tm' in line:
                last_tm = line
                text_block += line + '\n'
            if 'Tf' in line:
                splatted = line.split(' ')
                tf_index = splatted.index('Tf')
                last_font_resource = splatted[tf_index - 2]
                text_block += line + '\n'
            elif 'Td' in line:
                l = line.split(' ')
                # calculate the new tm
                last_tm = calculate_text_matrix(last_tm, line)
                # remove the Td from the line because it may contain another postscript operators
                l = l[0:-3]
                # put the tm after the Td line
                line = ' '.join(l)
                line = last_tm + '\n' + line
                text_block += line + '\n'
                prepend_space = False
            elif 'TD' in line:
                l = line.split(' ')
                # calculate the new tm 
                last_tm = calculate_text_matrix(last_tm, line)
                # remove the Td from the line because it may contain another postscript operators
                y_pos = round(float(line.split(' ')[-2]), 3)
                l = l[0:-3]
                last_tl = y_pos
                # put the tm after the Td line
                line = ' '.join(l)
                line = last_tm + '\n' + line
                text_block += line + '\n'
            elif 'T*' in line:
                td = f"0 {last_tl} td"
                tm = calculate_text_matrix(last_tm, td)
                last_tm = tm
                line = line.replace('T*', '')
                line = tm + '\n' + line
                text_block += line + '\n'
            elif 'Tj' in line:
                text += line
                # current_text_block_font_object = page_resources['/Font'][last_font_resource]
                # line = replace_cmap(line, current_text_block_font_object)

                # remove empty string in Tj
                if '( )Tj' in text:
                    prepend_space = True
                check_line = text
                check_line = check_line.replace('(', '')
                check_line = check_line.replace(')', '')
                check_line = check_line.replace('Tj', '')
                check_line = check_line.replace(' ', '')

                if len(check_line) != 0:
                    if prepend_space:
                        text = text[0] + ' ' + text[1:]
                        prepend_space = False
                    text_block += text + '\n'
                separated_text = False
                text = ''
            elif 'TJ' in line:
                line = line
                text += line
                converted_line = '(' + convert_TJ_to_Tj(text) + ')Tj\n'

                # current_text_block_font_object = page_resources['/Font'][last_font_resource]

                # converted_line = replace_cmap(converted_line, current_text_block_font_object)

                text_block += converted_line
                separated_text = False
                text = ''
            else:
                line = line
                if len(line) != 0 and (line[0] == '[' or separated_text == True):
                    text += line[0: -1]
                    separated_text = True
                elif (line[0] == '(' and line[-1] == '\\') or separated_text == True:
                    text += line[0: -1]
                    separated_text = True
                else:
                    text_block += line + '\n'
        else:
            result += text_block
            text_block = ''
            result += line + '\n'
    return result


# break the line positioned in the x position
def break_x_pos_line(line, widths, tm, font_size, media_box, height=0, column_x_position = 0):
    lines = ''
    line = line.replace('Tj', '').strip()
    line = (line[1:-1]).split(' ')
    size = 0
    splatted = tm.split(' ')
    tm_index = splatted.index('Tm')
    x_pos = round(float(splatted[tm_index - 2]), 3)
    line_one = ''
    line_two = ''
    for word in line:
        if not (size + calculate_string_width(widths, word, font_size) + x_pos > media_box[2]):
            size = size + calculate_string_width(widths, word, font_size)
            line_one += word + ' '
        else:
            line_two += word + ' '
    lines += tm + '\n'
    lines += '(' + line_one.strip() + ')Tj\n'

    # put new text matrix to position the new line
    splatted = tm.split(' ')
    tm_index = splatted.index('Tm')
    splatted[tm_index - 1] = str(round(float(splatted[tm_index - 1]), 3) - height)
    if column_x_position : splatted[tm_index - 2] = column_x_position
    # set new tm
    lines += ' '.join(splatted) + '\n'
    # appent the new line to the text
    lines += '(' + line_two.strip() + ')Tj\n'
    return lines


def fix_text_overflow(content, replacements, page_resources={}, media_box=[]):
    lines = content.splitlines()
    result = ''
    in_text = False
    last_tm = '1 0 0 1 0 0 Tm'
    last_tl = 0
    text_block = 'BT\n'
    text = ''
    last_font_resource = ''
    last_font_size = ''
    separated_text = False
    text_object = {}
    text_list = []
    last_col = 0
    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False

        if in_text:
            if 'Tm' in line:
                splatted = line.split(' ')
                tm_index = splatted.index('Tm')
                last_font_size = splatted[tm_index - 6]
                last_tm = line
                text_object.update({'Tm': line})
            if 'Tf' in line:
                splatted = line.split(' ')
                tf_index = splatted.index('Tf')
                last_font_resource = splatted[tf_index - 2]
                last_font_size = splatted[tf_index - 1]
                text_block += line + '\n'
                text_object.update({'Tf': line})
            if 'Tj' in line:
                text = line
                text = text.replace('Tj', '').strip()
                text = (text[1:-1]).strip()

                if len(text_list) > 0:
                    if not (text_list[-1].get('Tm').split(' ')[-2] == last_tm.split(' ')[-2]):
                        last_col = last_tm.split(' ')[-3]
                start_col = last_col
                
                if page_resources['/Font'][last_font_resource].get('/Widths') is not None:
                    str_width = calculate_string_width(page_resources['/Font'][last_font_resource]['/Widths'], text,
                                                       last_font_size)
                    splatted = last_tm.split(' ')
                    tm_index = splatted.index('Tm')
                    x_pos = round(float(splatted[tm_index - 2]), 3)
                    # calculate glyph height
                    glyph_height = (int(
                        page_resources['/Font'][last_font_resource]['/FontDescriptor']['/CapHeight']) * round(
                        float(last_font_size), 3)) / 1000
                    line_height = 12
                    if (str_width + x_pos) > media_box[2]:  # check if the text will go outside the page
                        new_line = break_x_pos_line(line, page_resources['/Font'][last_font_resource]['/Widths'],
                                                    last_tm, last_font_size, media_box, line_height + glyph_height, start_col)
                        text_block += new_line + '\n'
                    else:
                        text_block += line + '\n'

                text_object.update({'Tj': line})
                text_list.append(text_object)
                text_object = {}
            else:
                line = line.strip()
                if len(line) != 0 and (line[0] == '[' or separated_text == True):
                    text += line[0: -1]
                    separated_text = True
                else:
                    text_block += line + '\n'
        else:
            result += text_block
            text_block = ''
            result += line + '\n'
    return result

def get_word_resources_from_list(text_list, word_index, word):
    spread_text_list = []
    for item in text_list:
        for text in get_plain_text_from_tj_string(item.get('line')).split(' '):
            if text != '':
                spread_text_list.append({'font_size': item.get('font_size'), 'font_resource': item.get('font_resource'), 'word': text})
    if word_index > len(spread_text_list) - 1:
        return None
    return {'font_size': spread_text_list[word_index].get('font_size'), 'font_resource': spread_text_list[word_index].get('font_resource')}
            

def wrap_line(line, y_start, x_start, page_resources={}, media_box=[], text_list = [], line_height = 14):
    line_width = calculate_string_width_from_list(text_list, page_resources)

    col_width = float(media_box[2]) - 35 - x_start
    lines_count = math.ceil(line_width / col_width)

    lines = []
    current_line_size = 0
    splitted_line = line.split(' ')
    start_from_word_index = 0
    for i in range(0, lines_count):
        line_text = ''
        height = 0
        initial_font_size = text_list[0].get('font_size')
        initial_font_resource = text_list[0].get('font_resource')
        tj_list = []
        for word_index in range(start_from_word_index, len(splitted_line)):
            word = splitted_line[word_index]
            word_resources = get_word_resources_from_list(text_list, word_index, word)
            if word_resources == None:
                word_resources = {'font_size': initial_font_size, 'font_resource': initial_font_resource}
            if current_line_size + calculate_string_width(page_resources['/Font'][word_resources.get('font_resource')]['/Widths'], word, word_resources.get('font_size')) < col_width:
                current_line_size = current_line_size + calculate_string_width(page_resources['/Font'][word_resources.get('font_resource')]['/Widths'], word, word_resources.get('font_size'))

                if float(word_resources.get('font_size')) == float(initial_font_size) and word_resources.get('font_resource') == initial_font_resource:
                    line_text += word + ' '
                else : 
                    tj_list.append({'line' : line_text, 'font_size': initial_font_size, 'font_resource': initial_font_resource})
                    line_text = ''
                    line_text += word + ' '
                    initial_font_size = word_resources.get('font_size')
                    initial_font_resource = word_resources.get('font_resource')
            else:
                start_from_word_index = word_index
                current_line_size = 0
                break
        tj_list.append({'line' : line_text, 'font_size': initial_font_size, 'font_resource': initial_font_resource})
        lines.append(tj_list)

        if line.strip() == line_text.strip(): break
    content = ''

    line_y = 0
    line_x = 0
    line_font_resource = ''
    line_font_size = 0
    line_text = ''
    line_size = 0
    for line_index in range(0, len(lines)):
        line = lines[line_index]
        x_offset = 0
        for separated_text in line:
            font_size = separated_text.get('font_size')
            font_resource = separated_text.get('font_resource')
            glyph_height = (int(page_resources['/Font'][font_resource]['/FontDescriptor']['/CapHeight']) * round(float(font_size), 3)) / 1000
            # line_height = 14
            line_size = calculate_string_width(page_resources['/Font'][font_resource]['/Widths'], separated_text.get('line'), font_size)
            y = y_start - line_index * (line_height)
            x = x_start + x_offset
            x_offset = x_offset + line_size
            content += f"{font_resource} 1 Tf"+"\n"
            content += f"{font_size} 0 0 {font_size} {x} {y} Tm"+"\n"
            text = separated_text.get('line')
            content += f"({text})Tj"+"\n"
            line_y = y
            line_x = x
            line_font_resource = font_resource
            line_font_size = font_size
            line_text = text
            line_size = line_size

    # print({'content' : content, 'next_line_tm': next_line_tm})
    return {'content' : content, 'meta': {
        'y' : line_y,
        'x' : line_x, # starting position after adding the new line
        'font_resource' : line_font_resource,
        'font_size' : line_font_size,
        'text' : line_text,
        'line_size' : line_size,
    }}

def get_plain_text_from_tj_string(text):
    text = text.replace('Tj', '').strip()
    return text[1:-1]
    
def calculate_string_width_from_list(text_list, page_resources):
    width = 0
    for text_object in text_list:
        text = get_plain_text_from_tj_string(text_object.get('line'))
        font_size = text_object.get('font_size')
        font_resource = text_object.get('font_resource')
        width = width + calculate_string_width(page_resources['/Font'][font_resource]['/Widths'], text, font_size)
    return width

def check_text_overflow_from_list(text_list, line_y, page_resources={}, media_box=[], font_resource = '', font_size = 13):
    if len(text_list) == 0:
        return False
    
    line = ''
    for text in text_list: line += get_plain_text_from_tj_string(text.get('line')).strip() + ' '
    line = line.strip()
    
    line_width = calculate_string_width_from_list(text_list, page_resources)
    starting_offset = text_list[0].get('x')
    
    if (line_width + starting_offset) > media_box[2] - 20: # check if the text will go outside the page
        return {'will_overflow': True, 'line': line, 'y': line_y, 'x': text_list[0].get('x')}
    else:
        return {'will_overflow': False, 'line': line, 'y': line_y, 'x': text_list[0].get('x')}

def line_wrapping(content, replacements, page_resources={}, media_box=[]):
    lines = content.splitlines()
    text_map = {}
    result = ''
    in_text = False
    last_tm = '1 0 0 1 0 0 Tm'
    last_tl = 0
    text_block = 'BT\n'
    text = ''
    last_font_resource = ''
    last_font_size = ''
    separated_text = False
    text_object = {}
    text_list = []
    last_col = 0

    override_tm = False
    place_tm = ''

    for line in lines:
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False

        if in_text:
            if 'Tm' in line:
                splatted = line.split(' ')
                tm_index = splatted.index('Tm')
                last_font_size = splatted[tm_index - 6]
                last_tm = line
            if 'Tf' in line:
                splatted = line.split(' ')
                tf_index = splatted.index('Tf')
                last_font_resource = splatted[tf_index - 2]
                last_font_size = splatted[tf_index - 1]
            if ')Tj' in line:
                splitted = last_tm.split(' ')
                tm_index = splitted.index('Tm')
                line_position = float(splitted[tm_index - 1])
                line_content = text_map.get(line_position)
                tj_content = {'x': float(splitted[tm_index - 2]), 'y': float(splitted[tm_index - 1]), 'font_size': last_font_size, 'font_resource': last_font_resource, 'line': line}
                if line_content is None:
                    text_map.update({line_position: [tj_content]})
                else:
                    line_content.append(tj_content)
                    text_map.update({line_position: line_content})
    ignore_line_at_y = 0
    wrapper = ''

    for line_index in range(0, len(lines)):
        line = lines[line_index]
        if line == "BT":
            in_text = True

        elif line == "ET":
            in_text = False

        if in_text:
            if 'Tm' in line:
                splatted = line.split(' ')
                tm_index = splatted.index('Tm')
                last_font_size = splatted[tm_index - 6]
                last_tm = line
                text_object.update({'Tm': line})
            if 'Tf' in line:
                splatted = line.split(' ')
                tf_index = splatted.index('Tf')
                last_font_resource = splatted[tf_index - 2]
                last_font_size = splatted[tf_index - 1]
                text_block += line + '\n'
                text_object.update({'Tf': line})
            if ')Tj' in line:
                splitted = last_tm.split(' ')
                tm_index = splitted.index('Tm')
                current_text_line = float(splitted[tm_index - 1])
                line_contents = text_map.get(current_text_line)

                next_line = keyshift(text_map, current_text_line, -1)
                next_y = 0
                if next_line is not None:
                    next_y = next_line[0].get('y')
                line_y = line_contents[0].get('y')
                line_height = line_y - next_y if line_y - next_y >= 11 and line_y - next_y <= 16 else 14

                if (override_tm):
                    text_block += place_tm
                    override_tm = False
                    place_tm = ''
                    line_index = line_index - 1

                overflow = check_text_overflow_from_list(line_contents, current_text_line, page_resources, media_box, last_font_resource, last_font_size)
                ignore_line_at_y = overflow.get('y')
                if (overflow):
                    if overflow.get('will_overflow'):
                        if ignore_line_at_y == float(splitted[tm_index - 1]) and not (wrapper == overflow.get('line')):
                            wrapper = overflow.get('line')
                            wrap_result = wrap_line(overflow.get('line'), 
                                                    overflow.get('y'), 
                                                    overflow.get('x'), 
                                                    page_resources,
                                                    media_box, 
                                                    line_contents, 
                                                    line_height)

                            text_block += wrap_result.get('content')

                            y = wrap_result.get('meta').get('y')
                            x = wrap_result.get('meta').get('x')
                            font_size = wrap_result.get('meta').get('font_size')
                            line_size = wrap_result.get('meta').get('line_size')
                            if (
                                int(line_contents[0].get('x')) >= int(x) 
                                and wrap_result.get('meta').get('text').strip() != get_plain_text_from_tj_string(line_contents[0].get('line')).strip()
                            ):
                                override_tm = True
                                place_tm = f"{font_size} 0 0 {font_size} {int(x+line_size)} {y} Tm\n"
                            else :
                                override_tm = False
                    else:
                        text_block += line + '\n'
                else: 
                    text_block += line + '\n'
            else:
                line = line.strip()
                if len(line) != 0 and (line[0] == '[' or separated_text == True):
                    text += line[0: -1]
                    separated_text = True
                else:
                    text_block += line + '\n'
        else:
            result += text_block
            text_block = ''
            result += line + '\n'
    return result

def process_data(content, replacements, page_resources={}, media_box=[]):
    data = content.get_data()
    # ISO - 8859 - 1
    decoded_data = data.decode('cp1252')
    replaced_data = normalize_pdf(decoded_data, page_resources=page_resources)
    replaced_data = replace_text(replaced_data, replacements, page_resources, media_box=media_box)
    # replaced_data = fix_text_overflow(replaced_data, replacements, page_resources, media_box=media_box) # old line wrapping function should not be used
    replaced_data = line_wrapping(replaced_data, replacements, page_resources, media_box=media_box)

    encoded_data = replaced_data.encode('cp1252', errors='ignore')

    if content.decoded_self is not None:
        content.decoded_self.set_data(encoded_data)
    else:
        content.set_data(encoded_data)


def get_client():
    return client(
        's3',
        region_name=environ.get('S3_REGION_NAME', "ams3"),
        endpoint_url=environ.get('S3_ENDPOINT_URL', "https://ams3.digitaloceanspaces.com"),
        aws_access_key_id=environ.get('S3_ACCESS_ID', '7ZGKHBRJ3HICZQXAUMR4'),
        aws_secret_access_key=environ.get('S3_SECRET_KEY', 'TgoCc5fWwh+vxyzLkGWAPHZ20mLtknboyW8I5X6nJxU')
    )


def download_file(file_name):
    """
    Function to download a given file from an S3 bucket
    """
    s3 = resource('s3')
    output = f"{file_name}"
    s3.Bucket(environ.get('S3_BUCKET', "cec-ams3-prod")).download_file(file_name, output)

    return output
