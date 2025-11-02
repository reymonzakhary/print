import io
from typing import Optional
from urllib.request import urlopen, Request

from PyPDF2._utils import b_
from flask import request
from flask_restful import Resource
from zipfile import ZipFile
import os
from dotenv import load_dotenv
import time
import subprocess
from PyPDF2 import PdfReader, PdfWriter
from PyPDF2.generic import DecodedStreamObject, EncodedStreamObject, NameObject, ContentStream, ArrayObject, \
    TextStringObject
import requests
from helper.helper import process_data, replace_text
import uuid
import urllib.parse
import shutil, time

load_dotenv()
headers = {'User-Agent': "Magic Browser"}

class Replace(Resource):

    def post(self):
        # Production use enable this comment ###
        # data = request.form.to_dict(flat=True)

        data = request.get_json()
        # Get body request to be validated.
        path = data.get('path')
        url = data.get('url')
        search = data.get('search')
        replace = data.get('replace')

        replacements = {}
        for i in range(0, len(search)):
            if i < len(replace):
                replacements[str(search[i])] = replace[i]
            else:
                continue

        # return replacements
        disk = data.get('disk')

        # Only for development #
        if os.environ.get('CONFIG') == 'Development':
            # Check if path exists
            if not os.path.isfile(path):
                return {"message": "The requested path not found", "status": 422}, 200
            filename_base = path.replace(os.path.splitext(path)[1], "")
            # Prepare file
            pdf = PdfReader(path, strict=True)
            writer = PdfWriter()

            for page_number in range(0, len(pdf.pages)):
                page = pdf.pages[page_number]
                contents = page.get_contents()
                if isinstance(contents, DecodedStreamObject) or isinstance(contents, EncodedStreamObject):
                    process_data(contents, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])

                elif len(contents) > 0:
                    for obj in contents:
                        if isinstance(obj, DecodedStreamObject) or isinstance(obj, EncodedStreamObject):
                            streamObj = obj.getObject()
                            process_data(streamObj, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])

                # Force content replacement
                page[NameObject("/Contents")] = contents.decoded_self
                writer.add_page(page)

            with open(filename_base + ".result.pdf", 'wb') as out_file:
                writer.write(out_file)

            # Replace old file with the new one
            # os.remove(path)
            # os.rename(filename_base + ".result.pdf", filename_base + ".pdf")
        else:
            try:
                r = requests.get(url, allow_redirects=True)
            except:
                return {"message": "cannot open url", "status": 422}, 200
            download_path = "storage/render/" + str(uuid.uuid4())

            if not os.path.exists(download_path):
                os.makedirs(download_path)

            url = urllib.parse.unquote(url)
            filename = url.split('/')[-1]

            open(download_path + "/" + filename, 'wb').write(r.content)

            file = download_path + '/' + filename
            if not os.path.isfile(file):
                return {"message": "The requested path not found", "status": 422}, 200
            filename_base = file.replace(os.path.splitext(file)[1], "")
            # # Prepare file
            pdf = PdfReader(file)
            writer = PdfWriter()

            for page_number in range(0, len(pdf.pages)):
                page = pdf.pages[page_number]
                # print(page)
                contents: Optional[ContentStream] = page.get_contents()
                if isinstance(contents, DecodedStreamObject) or isinstance(contents, EncodedStreamObject):
                    process_data(contents, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])
                    # print(contents)
                elif isinstance(contents, ArrayObject):
                    content_object = page["/Contents"].get_object()
                    content = ContentStream(content_object, writer)
                    ###########################################################
                    # Convert stream to object EncodedStreamObject.#
                    ###########################################################
                    contents: Optional[EncodedStreamObject] = content.flate_encode()
                    process_data(contents, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])
                    ###########################################################
                    # End convert stream #
                    ###########################################################
                    # return {}

                    ###########################################################
                    # find and replace text from arrayobject pdf type encoding.#
                    ###########################################################
                    # print(contents.get_data())
                    
                    process_data(contents, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])
                    page[NameObject('/Contents')] = contents

                    # for operands, operator in content.operations:
                    #     if operator == b_("TJ") or operator == b_("Tj"):
                    #         text = operands[0]
                    #         for k, v in replacements.items():
                    #             if k in text:
                    #                 if len(v) > len(k):
                    #                     spaces = " " * (len(v) - len(k))
                    #                 replaced = text.replace(k,v)
                    #                 operands[0] = TextStringObject(replaced)
                    # # page.__setitem__(NameObject("/Contents"), content)
                    # page[NameObject('/Contents')] = content
                    ###########################################################
                    # END block code. #####################
                    ###########################################################

                elif len(contents) > 0:
                    for obj in contents:
                        if isinstance(obj, DecodedStreamObject) or isinstance(obj, EncodedStreamObject):
                            streamObj = obj.get_object()
                            process_data(streamObj, replacements, page_resources=page['/Resources'], media_box = page['/MediaBox'])

                # Force content replacement
                if isinstance(contents, DecodedStreamObject) or isinstance(contents, EncodedStreamObject):
                    page[NameObject("/Contents")] = contents.decoded_self

                writer.add_page(page)

            no_conflict = f"{str(int(time.time()))}"
            with open(filename_base + f".{no_conflict}.result.pdf", 'wb') as out_file:
                writer.write(out_file)
            # remove storage from path if exists
            upload_path = download_path.replace('storage', '')
            # prepare upload directory command
            upload_command = f"rclone copy \"{filename_base}.{no_conflict}.result.pdf\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{upload_path}\""
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_procoss:
                (out, err) = up_procoss.communicate()
            # wait until upload complete
            up_procoss.wait()

            # Replace old file with the new one
            shutil.rmtree(download_path)

            new_file_url = f"https://{os.environ.get('S3_BUCKET')}.{os.environ.get('S3_REGION_NAME')}.digitaloceanspaces.com{upload_path}"

            splatted = filename_base.split('/')
            filename = splatted[len(splatted) - 1]
            url = f"{new_file_url}/{filename}.{no_conflict}.result.pdf"
            return {"url": url, "message": "Success.", "status": 200}, 200