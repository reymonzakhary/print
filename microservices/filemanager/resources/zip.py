from flask import request
from flask_restful import Resource
from zipfile import ZipFile
import os
from dotenv import load_dotenv
import shutil
import time
import subprocess

load_dotenv()


class Extract(Resource):

    def post(self):
        data = request.form.to_dict(flat=True)
        # check if file exists on s3
        # data = request.get_json()
        path = data['disk']+'/'+data['file']
        to = data['disk']+'/'+data['to']
        output = []
        noConflectExtratDir = f"/noConflict-extracting-{time.time()}"
        # return to
        if os.environ.get('CONFIG') == 'Development':
            with ZipFile('storage/' + path, 'r') as zipObj:
                # Extract all the contents of zip file in current directory
                zipObj.extractall('storage/' + to + noConflectExtratDir)
                for root, dirs, files in os.walk("storage/" + to + noConflectExtratDir):
                    for filename in files:
                        if filename.startswith('.'):
                            pass
                        else:
                            uroot = root.replace("storage/" + to + noConflectExtratDir, to)
                            # Upload a file to your Space
                            if root + '/' + filename == "storage/" + path:
                                pass
                            else:
                                output.append(uroot + "/" + filename)
            shutil.rmtree("storage/" + to + noConflectExtratDir + "/__MACOSX")
            allfiles = os.listdir("storage/" + to + noConflectExtratDir)
            for f in allfiles:
                if os.path.exists("storage/" + to + "/" + f):
                    shutil.rmtree("storage/" + to + noConflectExtratDir)
                    return {"message": "We cloudn\'t extract this file, file already exists.", "paths": [],
                            "status": 400}, 200
                shutil.move("storage/" + to + noConflectExtratDir + "/" + f, "storage/" + to + "/" + f)
                # return root+"/"+filename
            shutil.rmtree("storage/" + to + noConflectExtratDir)
            return {"message": "file has been extracted successfully.", "paths": output, "status": 200}, 200
        else:
            try:
                # get dir name
                dir = os.path.dirname(path)
                path = path
                # preper download command
                download_command = f"rclone copy \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{path}\" \"storage/{dir}\""
                with subprocess.Popen(download_command, shell=True, stdout=subprocess.PIPE) as downProcess:
                    (downOut, downErr) = downProcess.communicate()
                    # wait until download complete
                downProcess.wait()
                paths = []

                # generate new directory with unique name to avoid unzip conflict
                noConflectExtratDir = f"/noConflict-extracting-{time.time()}"

                # unzip the file
                with ZipFile("storage/" + path, 'r') as zipObj:
                    # Extract all the contents of zip file in current directory
                    zipObj.extractall('storage/' + to + noConflectExtratDir)

                for root, dirs, files in os.walk("storage/" + to + noConflectExtratDir):
                    for filename in files:
                        if filename.startswith('.'):
                            pass
                        else:
                            uroot = root.replace("storage/" + to + noConflectExtratDir, to)
                            # Upload a file to your Space
                            if root + '/' + filename == "storage/" + path:
                                pass
                            else:
                                paths.append(uroot + "/" + filename)
                # preper upload directory command
                uploadCommand = f"rclone copy \"storage/{to}{noConflectExtratDir}/\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{to}/\""
                with subprocess.Popen(uploadCommand, shell=True, stdout=subprocess.PIPE) as upProcoss:
                    (out, err) = upProcoss.communicate()
                    # wait until upload complete
                upProcoss.wait()

                # return {'status':path}
                # delete local directory
                shutil.rmtree("storage/" + dir)
                # delete local zip file
                os.remove('storage/' + path)
                return {"message": "file has been extracted successfully.", "paths": paths, "status": 200}, 200
            except:
                return {"message": "We couldn\'t extracting the giving directory .", "paths": [], "status": 400}, 200


class Zip(Resource):

    def post(self):
        # check if file exists on s3
        # data = request.get_json()
        data = request.form.to_dict(flat=True)
        path = data['path']
        disk = data['disk'] if "disk" in data else "tenancy"
        name = data['name'] if "name" in data else ""
        put_path = data['putPath'] +"/"+name if (data['putPath'] in data and data['putPath'] != False) else path


        if os.environ.get('CONFIG') == 'Development':
            if os.path.exists('storage/' + path):
                shutil.make_archive('storage/' + path, 'zip', 'storage/' + path)
                return {"message": "file has been Zipped successfully.", "status": 200}, 200
            else:
                return {"message" : "File or folder not exists, in the giving path", "status": 404}, 404
        else:
            try:
                # download the directory to local

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
            except:

                return {"message": "We couldn\'t compress the giving file, please try again later.", "status": 400}, 200
        # make response 200 en json payload
