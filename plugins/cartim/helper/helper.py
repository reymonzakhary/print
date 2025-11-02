from flask import jsonify, session, request, Response
from deep_translator import GoogleTranslator
from translate import Translator
import os
import requests
import json
from bson import ObjectId
import re
import time
from collections import defaultdict

API_URL = "https://api.stg.print.com/"
CREDENTIALS = {
    "username": "berend@prindustry.nl",
    "password": "UD$SsV++@$?"
}
HEADERS = {
    "Accept": "application/json",
    "Content-Type": "application/json"
}

GOOGLE_LANGUAGES = {
    'af': 'afrikaans', 'sq': 'albanian', 'am': 'amharic', 'ar': 'arabic', 'hy': 'armenian',
    'as': 'assamese', 'ay': 'aymara', 'az': 'azerbaijani', 'bm': 'bambara', 'eu': 'basque',
    'be': 'belarusian', 'bn': 'bengali', 'bho': 'bhojpuri', 'bs': 'bosnian', 'bg': 'bulgarian',
    'ca': 'catalan', 'ceb': 'cebuano', 'ny': 'chichewa', 'zh-CN': 'chinese (simplified)',
    'zh-TW': 'chinese (traditional)', 'co': 'corsican', 'hr': 'croatian', 'cs': 'czech',
    'da': 'danish', 'dv': 'dhivehi', 'nl': 'dutch', 'en': 'english',
    'eo': 'esperanto', 'et': 'estonian', 'ee': 'ewe', 'tl': 'filipino', 'fi': 'finnish',
    'fr': 'french', 'fy': 'frisian', 'gl': 'galician', 'ka': 'georgian', 'de': 'german',
    'el': 'greek', 'gn': 'guarani', 'gu': 'gujarati', 'ht': 'haitian creole', 'ha': 'hausa',
    'haw': 'hawaiian', 'iw': 'hebrew', 'hi': 'hindi', 'hmn': 'hmong', 'hu': 'hungarian',
    'is': 'icelandic', 'ig': 'igbo', 'ilo': 'ilocano', 'id': 'indonesian', 'ga': 'irish',
    'it': 'italian', 'ja': 'japanese', 'kn': 'kannada', 'kk': 'kazakh',
    'km': 'khmer', 'rw': 'kinyarwanda', 'ko': 'korean',
    'ku': 'kurdish (kurmanji)', 'ckb': 'kurdish (sorani)', 'ky': 'kyrgyz', 'lo': 'lao',
    'la': 'latin', 'lv': 'latvian', 'ln': 'lingala', 'lt': 'lithuanian', 'lg': 'luganda',
    'lb': 'luxembourgish', 'mk': 'macedonian', 'mai': 'maithili', 'mg': 'malagasy',
    'ms': 'malay', 'ml': 'malayalam', 'mt': 'maltese', 'mi': 'maori', 'mr': 'marathi',
    'mni-Mtei': 'meiteilon (manipuri)', 'lus': 'mizo', 'mn': 'mongolian', 'my': 'myanmar',
    'ne': 'nepali', 'no': 'norwegian', 'or': 'odia (oriya)', 'om': 'oromo', 'ps': 'pashto',
    'fa': 'persian', 'pl': 'polish', 'pt': 'portuguese', 'pa': 'punjabi', 'qu': 'quechua',
    'ro': 'romanian', 'ru': 'russian', 'sm': 'samoan', 'sa': 'sanskrit', 'gd': 'scots gaelic',
    'nso': 'sepedi', 'sr': 'serbian', 'st': 'sesotho', 'sn': 'shona', 'sd': 'sindhi',
    'si': 'sinhala', 'sk': 'slovak', 'sl': 'slovenian', 'so': 'somali', 'es': 'spanish',
    'su': 'sundanese', 'sw': 'swahili', 'sv': 'swedish', 'tg': 'tajik', 'ta': 'tamil',
    'tt': 'tatar', 'te': 'telugu', 'th': 'thai', 'ti': 'tigrinya', 'ts': 'tsonga',
    'tr': 'turkish', 'tk': 'turkmen', 'ak': 'twi', 'uk': 'ukrainian', 'ur': 'urdu',
    'ug': 'uyghur', 'uz': 'uzbek', 'vi': 'vietnamese', 'cy': 'welsh', 'xh': 'xhosa',
    'yi': 'yiddish', 'yo': 'yoruba', 'zu': 'zulu'
}

DEFAULT_LANGUAGES = {
    'en': 'english', 'fr': 'french', 'de': 'German', 'nl': 'netherland', 'ar': 'Arabic'
}


def checkSimilarity(errorName, standardName):
    """

    Function to check similarity between two strings.

    Parameters:
        errorName (str): The error name to be checked for similarity.
        standardName (str): The standard name for comparison.

    Returns:
        dict: A dictionary containing information on the similarity between the two strings. The dictionary includes the following keys:
            - "errorName": The error name string.
            - "standardName": The standard name string.
            - "percentage": The percentage of similarity between the two strings.
            - "similarityChar": Concatenated characters indicating similarity and count.
            - "count": The count of similar characters.
            - "err": The error name string.
            - "stn": The standard name string.
            - "nameLen": The length of the error name.
            - "standardNameLen": The length of the standard name.
            - "standardNameList": List of characters in the standard name.
            - "errorNameList": List of characters in the error name.

    """
    # remove spaces
    errorName = str(errorName.strip().replace('-', ''))
    standardName = str(standardName.strip().replace('-', ''))
    # if any word in our db
    # for x in errorName.split():
    #     if(x.strip() == standardName):
    #         return {"percentage": 100, "char": x}
    # convert a new word to chars
    errorNameList = list(errorName)
    standardNameList = list(standardName)

    res = {}

    nameLen = len(errorNameList)
    standardNameLen = len(standardNameList)

    count = 0
    char = ""
    errLen = 0
    if (nameLen > int(standardNameLen) / 4):
        for Yindex, y in enumerate(errorNameList):
            # Yindex = errorNameList.index(y)
            for Cindex, c in enumerate(standardNameList):
                if (c == y):
                    # Cindex = standardNameList.index(c)

                    if (Cindex == Yindex):
                        count = count + 2
                    else:
                        count = count + 1
                    char = char + c + str(count) + ","
                    # standardNameList.remove(c)
                    standardNameList[Cindex] = ' '
                    break
    count = int(count / 2)

    past = nameLen
    if (standardNameLen > nameLen):
        past = standardNameLen

    percentage = int((count / past) * 100)

    if (percentage):
        res["errorName"] = errorName
        res["standardName"] = standardName
        res["percentage"] = percentage
        res["similarityChar"] = char
        res["count"] = count
        res["err"] = errorName
        res["stn"] = standardName
        res["nameLen"] = nameLen
        res["standardNameLen"] = standardNameLen
        res["standardNameList"] = standardNameList
        res["errorNameList"] = errorNameList

        return res


def authenticate_print_com():
    """
    This method authenticates the user to PrintCom by retrieving the authentication token from the session and printing it. If the authentication token is not available, it calls the login_to_print_com() function.
    """
    auth_token = session.get('auth_token')
    print(auth_token)

    if not auth_token:
        login_to_print_com()


def login_to_print_com():
    """

    This method sends a POST request to the login endpoint of print.com with the provided credentials. If the response status code is 200, it saves the authentication token in the session. If the response status code is not 200, it prints an error message for debugging purposes.

    """
    login_data = {"credentials": CREDENTIALS}

    response = requests.post(
        f"{API_URL}login",
        json=login_data,
        headers=HEADERS
    )

    if response.status_code == 200:
        session['auth_token'] = response.json()  # Save token in session
    else:
        print("Login failed:", response.text)  # Print error message for debugging


def generate_display_names(display_name: str, default=True):
    """

    Generate display names for supported languages based on the input display name.

    Parameters:
    display_name (str): The input display name from which to generate display names for languages.

    Returns:
    List[Dict[str, str]]: A list of dictionaries containing language ISO codes and their corresponding display names. The display names may be translated based on the current language settings.
    """
    supported_languages = DEFAULT_LANGUAGES.keys() if default else GOOGLE_LANGUAGES.keys()
    # supported_languages = DEFAULT_LANGUAGES.keys()
    translated_names = []

    for iso in supported_languages:
        translated_name = google_translate_text(display_name, iso)
        translated_names.append({"iso": iso, "display_name": translated_name})
        # time.sleep(1)  # Prevent hitting API limits

    return translated_names


def google_translate_text(text, target_language):
    """
    Translate the input text to the target language using Google Translate service.

    Parameters:
    text (str): The text to be translated.
    target_language (str): The target language code to translate the text into.

    Returns:
    str: The translated text in the target language. If an error occurs during translation, the original text is returned.
    """
    try:
        translated_text = GoogleTranslator(source='auto', target=target_language).translate(text)
        if "MYMEMORY WARNING" in translated_text:
            print("Translation limit reached. Returning original text.")
            return text  # Fallback to original
        return translated_text
    except Exception as e:
        print(f"Translation error: {e}")
        return text  # Fallback in case of errors


def remove_list_id_from_nested(data):
    if isinstance(data, dict):
        # If all keys are numeric (i.e., dictionary acting like a list), convert it to a list
        if all(isinstance(k, int) or k.isdigit() for k in data.keys()):
            return [remove_list_id_from_nested(v) for k, v in sorted(data.items(), key=lambda x: int(x[0]))]
        else:
            return {k: remove_list_id_from_nested(v) for k, v in data.items() if k != "listId"}
    elif isinstance(data, list):
        return [remove_list_id_from_nested(item) for item in data]
    return data


def convert_bracket_notation_to_nested_dict(data, keys_to_convert=None, keys_with_types=None):
    if keys_to_convert is None:
        keys_to_convert = {"_id", "id"}  # Default keys to convert to ObjectId
    if keys_with_types is None:
        keys_with_types = {}  # Dictionary of key names and expected types

    def get_nested():
        return defaultdict(get_nested)

    nested_dict = get_nested()

    for key, value in data.items():
        parts = re.split(r'\[|\]', key)  # Split by brackets
        parts = [p for p in parts if p]  # Remove empty strings

        current = nested_dict
        for i, part in enumerate(parts):
            if part.isdigit():  # Convert numeric keys to integers for lists
                part = int(part)

            if i == len(parts) - 1:  # Last key -> set value
                if part in keys_to_convert and isinstance(value, str):
                    try:
                        value = ObjectId(value)  # Convert to ObjectId
                    except:
                        pass  # Keep as string if invalid ObjectId
                elif part in keys_with_types:  # Enforce specified type
                    expected_type = keys_with_types[part]
                    try:
                        if expected_type == bool:
                            if isinstance(value, str):
                                value = value.strip().lower() in ("true", "1", "yes")
                            elif isinstance(value, (int, float)):  # Convert numbers like 1, 0, etc.
                                value = bool(value)
                        else:
                            value = expected_type(value)
                    except (ValueError, TypeError):
                        pass  # Keep original value if conversion fails

                current[part] = value
            else:
                if part not in current or not isinstance(current[part], dict):
                    current[part] = {}  # Ensure it remains a dictionary
                current = current[part]

    # Convert defaultdict to normal dict recursively
    def clean_dict(d):
        if isinstance(d, defaultdict):
            return {k: clean_dict(v) for k, v in d.items()}
        if isinstance(d, dict) and all(isinstance(k, int) for k in d.keys()):
            return [d[k] for k in sorted(d.keys())]
        return d  # Return ObjectId or any other type as is

    return clean_dict(nested_dict)


def system_key_to_object_d(value):
    """Convert value to ObjectId if it's a valid MongoDB ObjectId."""
    try:
        return ObjectId(value) if ObjectId.is_valid(value) else value
    except Exception:
        return value  # Return original value if conversion fails


def convert_object_id_fields(data, keys_to_convert):
    """Recursively converts specific keys in a nested JSON-like dictionary to ObjectId if they are valid"""
    if isinstance(data, dict):
        return {
            key: ObjectId(value) if key in keys_to_convert and isinstance(value, str) and len(value) == 24 else value
            for key, value in data.items()
        }
    elif isinstance(data, list):
        return [convert_object_id_fields(item, keys_to_convert) if isinstance(item, (dict, list)) else item for item in
                data]
    else:
        return data


def translate_text(text, target_language):
    """
    Translate the given text to the specified target language.

    Parameters:
    text (str): The text to be translated.
    target_language (str): The target language code to translate the text into.

    Returns:
    str: The translated text in the specified target language. If an error occurs during translation, a string with the error message is returned.
    """
    try:
        translator = Translator(to_lang=target_language)
        return translator.translate(text)
    except Exception as e:
        return f"Translation error: {e}"


def format_text(text):
    """Convert underscore-separated text to normal capitalized text."""
    return " ".join(word.capitalize() for word in text.split("_"))


def build_slug_to_id_map(properties):
    """
    Builds a mapping of slug -> option_id from the given properties.

    :param properties: List of property dictionaries containing options.
    :return: Dictionary mapping each slug to its corresponding option_id.
    """
    slug_to_id = {}
    for prop in properties:
        for option in prop.get("options", []):
            slug_to_id[option["slug"]] = option.get("option_id", option["slug"])  # Fallback to slug if no option_id
    return slug_to_id


def replace_exclusions_with_ids(properties, slug_to_id_map):
    """
    Replaces slugs in the 'excludes' arrays with their corresponding option IDs.

    :param properties: List of property dictionaries containing options.
    :param slug_to_id_map: Dictionary mapping slugs to option IDs.
    :return: Updated properties list with modified 'excludes' arrays.
    """
    for prop in properties:
        for option in prop.get("options", []):
            if "excludes" in option:
                updated_excludes = []
                for exclusion_pair in option["excludes"]:
                    updated_pair = [slug_to_id_map.get(slug, slug) for slug in exclusion_pair]
                    updated_excludes.append(updated_pair)
                option["excludes"] = updated_excludes
    return properties


def has_nullable_option(data, property_slug):
    """
    Checks if any option under a given property has `nullable: true`.

    :param data: Dictionary containing the JSON data.
    :param property_slug: The slug of the property to check.
    :return: Boolean indicating if any option has `nullable: true`.
    """
    property_data = next((prop for prop in data.get("properties", []) if prop.get("slug") == property_slug), None)

    if not property_data:
        return False  # Property not found
    options = property_data.get('options', [])
    has_nullable = any(option.get("nullable", True) for option in options)

    return has_nullable and property_data.get('locked', True)


def is_reseller_dropdown(data, property_slug):
    property_data = next((prop for prop in data.get("properties", []) if prop.get("slug") == property_slug), None)
    if not property_data:
        return False

    return property_data.get('viewMode', {}).get('reseller') == "dropdown"
