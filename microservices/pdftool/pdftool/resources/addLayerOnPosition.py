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
import fitz
from PyPDF2 import PdfWriter, PdfReader, Transformation, PageObject

load_dotenv()

def stamp_layer(
    content_pdf: Path,
    stamp_pdf: Path,
    page,
    tx,
    ty,
    pdf_result: Path
):
    
    reader = PdfReader(stamp_pdf)
    image_page = reader.pages[0]
    origin = PdfReader(content_pdf)
    mediabox = origin.pages[0].mediabox

    page_obj = PageObject()
    blank_stamp = page_obj.create_blank_page(width=mediabox[-2], height=mediabox[-1])
    blank_stamp.merge_page(image_page)

    placeSignature = Transformation().translate(tx, ty)
    blank_stamp.add_transformation(placeSignature)

    writer = PdfWriter()

    reader = PdfReader(content_pdf)
    for current_page in range(0, len(reader.pages)):
        if current_page == (page - 1):
            content_page = reader.pages[current_page]
            mediabox = content_page.mediabox
            content_page.merge_page(blank_stamp)
            content_page.mediabox = mediabox
            writer.add_page(content_page)
        else:
            content_page = reader.pages[current_page]
            writer.add_page(content_page)
    with open(pdf_result, "wb") as fp:
        writer.write(fp)

def find_text_positions(text, page_number, pdf_path):
    reader = PdfReader(pdf_path)
    pyPdfPage = reader.pages[page_number-1]
    box = list(pyPdfPage.mediabox)

    ### READ IN PDF
    doc = fitz.open(pdf_path)
    count = 1
    for page in doc:
        if page_number == count:
            ### SEARCH
            text_instances = page.search_for(text)

            ### HIGHLIGHT
            for inst in text_instances:
                return {'x': float(inst.bottom_left[0]), 'y': float(inst.bottom_left[1]), 'height': float(box[3])}
        
        count = count + 1
    return {'x': 0, 'y': 0, 'height': 0}

class AddLayerToPosition(Resource):

    def post(self):
        # check if file exists on s3
        # data = request.get_json()
        if os.environ.get('CONFIG') == 'Development':
            
            data = request.get_json()

            origin = data.get('origin')
            stamp = data.get('stamp')
            x = data.get('x')
            y = data.get('y')
            output = data.get('output')
            page = data.get('page')

            if os.path.exists(origin):
                # return {"path" : origin}
                stamp_layer(origin, stamp, page, x, y, output)
                return {"path": output, "message": "file has been stamped successfully.", "status": 200}, 200
            else:
                return {"message" : "File or folder not exists, in the giving path", "status": 404}, 404
        else:
            data = request.get_json()

            origin = data.get('origin')
            stamp = data.get('stamp')
            x = data.get('x')
            y = data.get('y')
            page = data.get('page')
            act = data.get('act')
            search = data.get('search')

            # download the pdf
            r = requests.get(origin, allow_redirects=False)
            no_conflict = str(uuid.uuid4())
            download_path = "storage/render/" + no_conflict

            if not os.path.exists(download_path):
                os.makedirs(download_path)
            origin = urllib.parse.unquote(origin)
            filename = origin.split('/')[-1]
            origin_file = download_path + "/" + filename
            open(origin_file, 'wb').write(r.content)

            # download the watermark or the signature (stamp)
            r = requests.get(stamp, allow_redirects=True)
            no_conflict = str(uuid.uuid4())
            stamp_download_path = "storage/render/" + no_conflict

            if not os.path.exists(stamp_download_path):
                os.makedirs(stamp_download_path)
            stamp = urllib.parse.unquote(stamp)
            filename = stamp.split('/')[-1]
            stamp_file = download_path + "/" + filename
            open(stamp_file, 'wb').write(r.content)

            ''' Get pdf file size '''
            reader = PdfReader(f"{stamp_file}")
            box = reader.pages[0].mediabox
            # w = float(box.width())
            w = box[2]
            h = box[3]

            output_file_name = f'/{str(int(time.time()))}.recovery.pdf'
            recovered_file_name = output_file_name.replace('recovery', 'output')
            
            if act:
                position = find_text_positions(search, page, origin_file)
                stamp_layer(origin_file, stamp_file, page, x, position.get('height') - position.get('y') - (float(h)/2), f"{download_path}{output_file_name}")
            else:
                # call stamp layer function to add the signature on the position
                stamp_layer(origin_file, stamp_file, page, x, Decimal(y) - (Decimal(h)/2), f"{download_path}{output_file_name}")

                #recover the output from corruption and damage
                bashCommand = f"gs -o {download_path}{recovered_file_name} -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress {download_path}{output_file_name}"
                with subprocess.Popen(bashCommand, shell=True, stdout=subprocess.PIPE) as recovery_process:
                    (out, err) = recovery_process.communicate()
                # wait until upload complete
                recovery_process.wait()

            upload_path = download_path.replace('storage', '')
            # prepare upload directory command
            upload_command = f"rclone copy \"{download_path}{recovered_file_name}\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{upload_path}\"".replace('//', '/')
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_process:
                (out, err) = up_process.communicate()
            # wait until upload complete
            up_process.wait()

            shutil.rmtree(download_path)

            new_file_url = f"https://{os.environ.get('S3_BUCKET')}.{os.environ.get('S3_REGION_NAME')}.digitaloceanspaces.com{upload_path}"
            url = new_file_url + recovered_file_name
            
            return {"url": url, "message": "Success.", "status": 200}, 200



