from flask import Response, request, jsonify
from models.category import Category
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import requests
import json
import datetime
import math
import itertools
import collections


def get_children(children, obj, options, box_name, parent):     
    
    for child in children:
        if 'children' in child and child['children']:
            if child["is_parent"] == False:

                options.append(
                    {'parent': parent, 'box': box_name, 'option': child['code']})

            get_children(child['children'], obj,
                         options, child['code'], box_name)

        else:
            options.append(
                {'parent': parent, 'box': box_name, 'option': child['code']})

def multiplay(Lists):
    cartesian_product = itertools.product(*Lists)
    cartesian_list = list(cartesian_product)

    return cartesian_list

def combination(Lists, option_box):
    # Lists = dict(reversed(list(Lists.items())))
    combined_options = {}
    opts = []
    
    for x in Lists.items():
        for xy in x[1]:
            level = []
            for xyz in xy:
                if xyz in Lists:
                    level2 = []
                    for j in Lists[xyz]:
                        opt = list(set(xy) | set(j))
                        # return xyz
                        boxes = []
                        apd = 'yes'
                        for o in opt:
                            if option_box[o] in boxes:
                                apd = 'no'
                            boxes.append(option_box[o])
                        if apd == 'yes':
                            level2.append(opt)
                    level.append(level2)
                else:
                    level.append([[xyz]])
                # opts.append(level)

            opts.append(multiplay(level))
            # level = list(itertools.chain.from_iterable(level))
        if opts:
            combined_options[x[0]] = opts
        opts = []

    return merge_optins(combined_options, option_box)

def merge_optins(lists, option_box):
    combined_options = {}
    opts = []
    rm = []
    for x in lists.items():
        level = []
        level1 = []
        for xy in x[1]:
            for i in xy:
                level1.append(len(i))
                # if x[0] != 'root':
                    # return i[0]
                if len(i) == 1:
                    level.append(i[0])
                elif len(i) == 2:
                    level.append(list(set(i[0]) | set(i[1])))
                elif len(i) == 3:
                    level.append(list(set(i[0]) | set(i[1]) | set(i[2])))
                elif len(i) == 4:
                    level.append(list(set(i[0]) | set(i[1]) | set(i[2]) | set(i[3])))
                elif len(i) == 5:
                    level.append(list(set(i[0]) | set(i[1]) | set(i[2]) | set(i[3]) | set(i[4])))
                elif len(i) == 6:
                    level.append(list(set(i[0]) | set(i[1]) | set(i[2]) | set(i[3]) | set(i[4]) | set(i[5])))
        # return level1
        combined_options[x[0]] = level
    return combined_options

def combination2(Lists, option_box):
    # Lists = dict(reversed(list(Lists.items())))
    combined_options = {}
    opts = []
    rm = []
    main_boxes = ['film', 'material', 'ink-option', 'brush-direction', 'finishing']
    for x in Lists.items():
        if x[0] in option_box and option_box[x[0]] in main_boxes:

            for xy in x[1]:
                level = []
                for xyz in xy:
                    if xyz in Lists:
                        level2 = []
                        for j in Lists[xyz]:
                            opt = list(set(xy) | set(j))
                            # return xyz
                            boxes = []
                            apd = 'yes'
                            
                            for o in opt:
                                if option_box[o] in boxes:
                                    apd = 'no'
                                boxes.append(option_box[o])
                            if apd == 'yes':
                                level2 = list(set(level2) | set(opt))
                                # level2.append(opt)
                        # rm.append(xyz)
                        if level2:
                            level = list(set(level) | set(level2))
                if level:
                    opts.append(level)
                # level = list(itertools.chain.from_iterable(level))

        if opts and x[0] not in list(dict.fromkeys(rm)):
            combined_options[x[0]] = opts
            opts = []
    # rem = []
    # for z in combined_options.items():
    #     for zz in z[1]:

    #         rem.append(zz)
    # de = []
    # for z in combined_options.items():
    #     for rm in z[1]:
    #         for t in rem:
    #             br = 'stay'
    #             for zi in combined_options.items():
    #                 for zzi in zi[1]:
    #                     if len(rm) > len(zzi):
    #                         de.append(zi[0])
    #                         br = 'go'
    #                         break

    combined_options['root'] = Lists['root']    
    return combined_options

def combination3(Lists, option_box):
    # Lists = dict(reversed(list(Lists.items())))
    combined_options = {}
    opts = []
    for x in Lists.items():
        if x[0] == 'root':
            for xy in x[1]:
                level = []
                for xyz in xy:
                    if xyz in Lists:
                        level2 = []
                        for j in Lists[xyz]:
                            opt = list(set(xy) | set(j))
                            # return xyz
                            boxes = []
                            apd = 'yes'
                            for o in opt:
                                if option_box[o] in boxes:
                                    apd = 'no'
                                boxes.append(option_box[o])
                            if apd == 'yes':
                                level2.append(opt)
                        level.append(level2)
                    else:
                        level.append([[xyz]])
                    # opts.append(level)

                opts.append(multiplay(level))

            if opts:
                combined_options[x[0]] = opts
            opts = []

    return merge_optins(combined_options, option_box)

class Configure(Resource):

    def post(self):
        data = request.get_json()

        options = []
        asd = []

        obj = {}
        for box in data['options']:
            if box['code'] == 'quantity' or box['code'] == 'bundle' or box['code'] == 'accessories-cross-sell':
                pass
            else:
                if 'children' in box:
                    get_children(box['children'], obj,
                                 options, box['code'], 'root')

        # return options
        options_parent = {}
        opts = []
        option_box = {}
        for opt in options:
            opts.append(opt['option'])
            option_box[opt['option']] = opt['box']
            if opt['parent'] in options_parent:
                options_parent[opt['parent']].append(opt)
            else:
                options_parent[opt['parent']] = [opt]
            opts = []

        # return options_parent

        options_parent_box = {}
        option = {}
        for x in options_parent.items():
            for y in x[1]:
                if y['box'] in option:
                    if y['box'] == 'size' and y['option'] == 'height':
                        pass
                    else:
                        option[y['box']].append(y['option'])
                else:
                    option[y['box']] = [y['option']]

            if x[0] in options_parent_box:
                options_parent_box[x[0]].append(option)
            else:
                options_parent_box[x[0]] = [option]
            option = {}

        # return options_parent_box
        
        multiplayed = {}
        BM = []
        for xy in options_parent_box.items():
            for xyz in xy[1][0].items():
                BM.append(xyz[1])
            
            multiplayed[xy[0]] = multiplay(BM)
            BM = []
        # return multiplayed

    
        combo = combination(multiplayed, option_box)
        # return combo
        # combo = dict(reversed(list(combo.items())))

        combo =  combination2(combo, option_box) 
        return combo
        
        combo =  combination(combo, option_box) 
        return combo
        # return list(combo['root'] for combo['root'],_ in itertools.groupby(combo['root']))

        resJson = {
            "status": 200,
            "data": obj
        }

        return jsonify(options, product)
