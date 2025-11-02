from decimal import Decimal

from flask import request
from flask_restful import Resource
from zipfile import ZipFile
import os
from dotenv import load_dotenv
import shutil
import time
import subprocess
import uuid
from pathlib import Path
import urllib.parse
import requests
import time

from PyPDF2 import PdfWriter, PdfReader, PageObject

load_dotenv()


def add_layers(
        template: Path,
        layers_directory: Path,
        layers_options: list,
        output_directory: Path,
):
    pdf = PdfReader(template)
    writer = PdfWriter()
    for page_number in range(0, len(pdf.pages)):
        page = pdf.pages[page_number]
        mediabox = page.mediabox
        # start add layers on blank page

        # filter background layers
        background_layers = []
        for layer in layers_options:
            if layer.get('position') == 'background':
                background_layers.append(layer)

        # filter stamp layers
        stamp_layers = []
        for layer in layers_options:
            if layer.get('position') == 'stamp':
                stamp_layers.append(layer)

        page_obj = PageObject()
        # add layers as background
        blank_background = page_obj.create_blank_page(width=mediabox[-2], height=mediabox[-1])
        for layer in background_layers:
            if (layer.get('page') != None and (layer.get('page') != "")) and layer.get('page') - 1 != page_number:
                continue
            layer_filename = layer.get('path').split('/')[-1]
            layer_object = PdfReader(layers_directory + '/' + layer_filename)
            if layer.get('page') != None:
                layer_object = layer_object.pages[0]
            else:
                layer_object = layer_object.pages[page_number % len(layer_object.pages)]

            blank_background.merge_page(layer_object)

        # add layers as stamp
        blank_stamp = page_obj.create_blank_page(width=mediabox[-2], height=mediabox[-1])
        for layer in stamp_layers:
            if layer.get('page') != None and layer.get('page') - 1 != page_number:
                continue
            layer_filename = layer.get('path').split('/')[-1]
            layer_object = PdfReader(layers_directory + '/' + layer_filename)
            if layer.get('page') != None:
                layer_object = layer_object.pages[0]
            else:
                layer_object = layer_object.pages[page_number % len(layer_object.pages)]

            blank_stamp.merge_page(layer_object)

        # combine layers
        page_to_be_added = page_obj.create_blank_page(width=mediabox[-2], height=mediabox[-1])
        page_to_be_added.merge_page(blank_background)
        page_to_be_added.merge_page(page)
        page_to_be_added.merge_page(blank_stamp)
        # add the new page to the pdf
        page_to_be_added.mediabox = mediabox
        writer.add_page(page_to_be_added)

        # get file name
        filename = template.split('/')[-1]
        with open(output_directory + '/' + filename, "wb") as fp:
            writer.write(fp)


def get_layers(layers_directory, local_path):
    ''' Download layers directory   '''
#     os.makedirs(local_path)
    for layer_object in layers_directory:
        download_command = f"rclone copy \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{layer_object.get('disk')}/{layer_object.get('path')}\" \"{local_path}\""
        with subprocess.Popen(download_command, shell=True, stdout=subprocess.PIPE) as downProcess:
            (downOut, downErr) = downProcess.communicate()
            # wait until download complete
        downProcess.wait()
    return local_path


class AddLayersOnPdfs(Resource):

    def post(self):
        data = request.get_json()
        layers_options = data.get('layers')
        templates_directory = data.get('templates')
        layers_directory = data.get('layers_directory')
        output_directory = data.get('destination')
        disk = data.get('disk')

        if os.environ.get('CONFIG') == 'Development':

            if not os.path.isdir(layers_directory): return {'message': 'Layers directory doesn\'t exists',
                                                            'status': 422}, 200

            if not os.path.isdir(templates_directory): return {'message': 'Templates directory doesn\'t exists',
                                                               'status': 422}, 200

            # create output directory if not exists
            if not os.path.isdir(output_directory):
                os.makedirs(output_directory)

            for path in os.listdir(templates_directory):
                # the path is file and then stamp layer on it
                if os.path.isfile(templates_directory + '/' + path):
                    template = templates_directory + '/' + path
                    add_layers(template, layers_directory, layers_options, output_directory)
                # skip if the path is directory
                else:
                    continue

            return {"path": output_directory, "message": "Layers has been added sucessfully.", "status": 200}, 200
        else:

            no_conflict_extract_dir = f"noConflict-extracting-{time.time()}"
            templates_local_path = f"storage/{no_conflict_extract_dir}/templates/"
            layers_local_path = f"storage/{no_conflict_extract_dir}/layers/"
            output_local_path = f"storage/{no_conflict_extract_dir}/output/"

            # create directories
            os.makedirs(f"storage/{no_conflict_extract_dir}/")
            os.makedirs(f"{templates_local_path}")
            os.makedirs(f"{layers_local_path}")
            os.makedirs(f"{output_local_path}")


            ''' Download templates directory   '''
            if templates_directory[-1] != '/':
                templates_directory = templates_directory + '/'
            download_command = f"rclone copy \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{templates_directory}\" \"{templates_local_path}\""
            with subprocess.Popen(download_command, shell=True, stdout=subprocess.PIPE) as downProcess:
                (downOut, downErr) = downProcess.communicate()
                # wait until download complete
            downProcess.wait()

            get_layers(layers_options, layers_local_path)

            # apply addlayers
            for path in os.listdir(templates_local_path):
                # the path is file and then add layer on it
                if os.path.isfile(templates_local_path + '/' + path):
                    template = templates_local_path + '/' + path
                    add_layers(template, layers_local_path, layers_options, output_local_path)
                # skip if the path is directory
                else:
                    continue

            upload_command = f"rclone copy \"{output_local_path}\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{output_directory}\""
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_process:
                (out, err) = up_process.communicate()
                # wait until upload complete
            up_process.wait()

            # Replace old file with the new one
            shutil.rmtree(f"storage/{no_conflict_extract_dir}")

            return {"message": "Layers added successfully", "status": 200}, 200
