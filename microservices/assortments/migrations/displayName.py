def display_name(item_dict):
    lang_list = ['en', 'fr', 'nl', 'de']
    data = {}
    if "display_name" in item_dict:
        # if we have as text
        if type(item_dict['display_name']) == str:
            data['display_name'] = [{"display_name": item_dict['display_name'], "iso": lang} for lang in lang_list]
        # check if it list
        elif type(item_dict['display_name']) == list:
            # if iso is exists pass it
            data['display_name'] = []
            for lang in lang_list:
                result = list(filter(lambda x: (x['iso'] == lang), item_dict['display_name']))
                if result:
                    data['display_name'].append({
                        'display_name': result[0]['display_name'],
                        'iso': result[0]['iso'],
                    })
                else:
                    data['display_name'].append({
                        'display_name': item_dict['name'],
                        'iso': lang,
                    })
    else:
        data['display_name'] = [{"display_name": item_dict['name'], "iso": lang} for lang in lang_list]
    return data['display_name']