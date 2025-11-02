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

from PyPDF2 import PdfWriter, PdfReader, Transformation

load_dotenv()

def stamp_layer(
    content_pdf: Path,
    stamp_pdf: Path,
    tx,
    ty,
    pdf_result: Path
):
    
    reader = PdfReader(stamp_pdf)
    image_page = reader.pages[0]

    # stamp signature to a blank transparent A4 pdf file
    reader = PdfReader(Path('storage/test_addlayer/a.pdf'))
    blank_pdf = reader.pages[0]
    blank_mediabox = blank_pdf.mediabox
    blank_pdf.merge_page(image_page)
    blank_pdf.mediabox = blank_mediabox
    writer = PdfWriter()
    writer.add_page(blank_pdf)

    tmp_placement_layer_helper = uuid.uuid4().hex
    tmp_placement_layer_helper = 'storage/test_addlayer/' + tmp_placement_layer_helper + '.pdf'
    with open(Path(tmp_placement_layer_helper), 'wb') as placement_helper:
        writer.write(placement_helper)

    # adjust placement of the signature
    reader = PdfReader(Path(tmp_placement_layer_helper))
    tmp_pdf = reader.pages[0]
    placeSignature = Transformation().translate(tx, ty)
    tmp_pdf.add_transformation(placeSignature)

    writer = PdfWriter()

    reader = PdfReader(content_pdf)
    content_page = reader.pages[0]
    mediabox = content_page.mediabox
    content_page.merge_page(tmp_pdf)
    content_page.mediabox = mediabox
    writer.add_page(content_page)
    os.remove(tmp_placement_layer_helper)
    with open(pdf_result, "wb") as fp:
        writer.write(fp)

class AddLayerToPosition(Resource):

    def post(self):
        # check if file exists on s3
        # data = request.get_json()

        if os.environ.get('CONFIG') == 'Development':
            
            data = request.form.to_dict(flat=True)
            origin = Path(data['origin'])
            stamp = Path(data['stamp'])
            x = float(data['x'])
            y = float(data['y'])
            output = Path(data['output'])

            if os.path.exists(origin):
                # return {"path" : ""}
                stamp_layer(origin, stamp, x, y, output)
                return {"message": "file has been Zipped successfully.", "status": 200}, 200
            else:
                return {"message" : "File or folder not exists, in the giving path", "status": 404}, 404
        else:
            # try:
                # download the directory to local
                data = request.form.to_dict(flat=True)
                
                origin = os.path.join(data['origin_disk'], data['origin_path'], data['origin_filename'])
                return {"origin" : origin}
                stamp = Path(data['stamp'])
                x = float(data['x'])
                y = float(data['y'])
                output = Path(data['output'])


                path = disk+"/"+path
                put_path = disk+"/"+put_path
                down_command = f"rclone sync \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{path}\" \"storage/{path}\""
                with subprocess.Popen(down_command, shell=True, stdout=subprocess.PIPE) as down_process:
                    (out, err) = down_process.communicate()
                down_process.wait()

                shutil.make_archive('storage/' + put_path, 'zip', 'storage/' + path)
                # preper upload directory command
                dir = os.path.dirname(put_path)
                upload_command = f"rclone sync \"storage/{put_path}.zip\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{dir}\""
                with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_processor:
                    (out, err) = up_processor.communicate()
                    # wait until upload complete
                up_processor.wait()
                # Upload a file to your Space
                shutil.rmtree('storage/' + path)
                os.remove('storage/' + put_path+".zip")
                return {"message": "File has been Zipped successfully.", "status": 200}, 200
            # except:

            #     return {"message": "We couldn\'t compress the giving file, please try again later.", "status": 400}, 200
        # make response 200 en json payload
