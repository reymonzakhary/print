import io
import shutil
from cmath import isclose
from flask import request
from flask_restful import Resource
import os
from dotenv import load_dotenv
import time
import subprocess
from PyPDF2 import PdfMerger, PdfReader
import re
from itertools import groupby

load_dotenv()


class MergeMultipleFiles(Resource):

    def post(self):
        # Production use enable this comment ###
        # data = request.form.to_dict(flat=True)
        data = request.get_json()
        directory = data.get('directory')
        disk = data.get('disk')
        destinations = data.get('destinations')
        filename = data.get('filename')
        separate = data.get('separate')

        no_conflict_extract_dir = f"/noConflict-extracting-{time.time()}"

        if not os.path.exists(f"storage/{no_conflict_extract_dir}/{directory}/"):
            os.makedirs(f"storage/{no_conflict_extract_dir}/{directory}/")

        local_dir = f"storage/{no_conflict_extract_dir}/{directory}"

        ''' Download directory   '''
        download_command = f"rclone copy \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{directory}\" \"{local_dir}\""
        with subprocess.Popen(download_command, shell=True, stdout=subprocess.PIPE) as downProcess:
            (downOut, downErr) = downProcess.communicate()
            # wait until download complete
        downProcess.wait()

        ''' List all files '''
        x = [a for a in os.listdir(local_dir) if a.endswith(".pdf")]
        if not separate == True:
            x = sorted(x, reverse=True)
            x = sorted(x, key=lambda s: int(0 if re.search(r'\d{3,}', s) is None else re.search(r'\d{3,}', s).group()))
        else:
            x = sorted(x, key=lambda s: int(0 if re.search(r'\d+', s) is None else re.search(r'\d+', s).group()))

        path = f"{local_dir}/result"
        a4_list = []
        a3_list = []
        result = []

        for pdf in x:
            if separate:
                ''' Get pdf file size '''
                reader = PdfReader(f"{local_dir}/{pdf}")
                try:
                    box = reader.pages[0].mediabox
                    # w = box.width()
                    w = box[2]
                    h = box[3]
                    if isclose(round(w), round(595.28)) and isclose(round(h), round(841.89)):
                        a4_list.append(pdf)
                        if not os.path.exists(f"{path}/A4"):
                            os.makedirs(f"{path}/A4")
                    elif isclose(round(w), round(1190.55)) and isclose(round(h), round(841.89)):
                        a3_list.append(pdf)
                        if not os.path.exists(f"{path}/A3"):
                            os.makedirs(f"{path}/A3")
                except:
                    return {"message": f"Cannot open the file {pdf}", "status": 422}, 200

        if not os.path.exists(path):
            os.makedirs(path)

        if len(a4_list):
            ''' Merges all the pdf files in current directory '''
            merger = PdfMerger()

            for a4_pdf in a4_list:
                merger.append(open(f"{local_dir}/{a4_pdf}", 'rb'))

            with open(f"{path}/A4/{filename}", "wb") as new_file:
                merger.write(new_file)
            ''' Upload the current directory '''
            upload_command = f"rclone copy \"{path}/A4\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{directory}/A4\""
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_process:
                (out, err) = up_process.communicate()
                # wait until upload complete
            up_process.wait()
            result.append({
                "disk": disk,
                "path": f"{directory}/A4/{filename}",
                "destinations": f"{destinations}/A4",
                "dir": "A4"
            })

        if len(a3_list):
            ''' Merges all the pdf files in current directory '''
            merger = PdfMerger()

            for pdf in a3_list:
                merger.append(open(f"{local_dir}/{pdf}", 'rb'))

            with open(f"{path}/A3/{filename}", "wb") as new_file:
                merger.write(new_file)

            ''' Upload the current directory '''
            upload_command = f"rclone copy \"{path}/A3\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{directory}/A3\""
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_process:
                (out, err) = up_process.communicate()
                # wait until upload complete
            up_process.wait()
            result.append({
                "disk": disk,
                "path": f"{directory}/A3/{filename}",
                "destinations": f"{destinations}/A3",
                "dir": "A3"
            })

        if len(a3_list) == 0 and len(a4_list) == 0:
            ''' Merges all the pdf files in current directory '''
            merger = PdfMerger()

            for pdf in x:
                merger.append(open(f"{local_dir}/{pdf}", 'rb'))

            with open(f"{path}/{filename}", "wb") as new_file:
                merger.write(new_file)

            ''' Upload the current directory '''
            upload_command = f"rclone copy \"{path}\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{disk}/{directory}\""
            with subprocess.Popen(upload_command, shell=True, stdout=subprocess.PIPE) as up_process:
                (out, err) = up_process.communicate()
                # wait until upload complete
            up_process.wait()
            result.append({
                "disk": disk,
                "path": f"{directory}/{filename}",
                "destinations": f"{destinations}",
                "dir": ""
            })

        # delete local directory
        shutil.rmtree(local_dir)
        return {"message": "file has been merged successfully.", "results": result, "status": 200}, 200
