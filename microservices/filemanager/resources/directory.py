from flask import request
from flask_restful import Resource
import os
from dotenv import load_dotenv
import subprocess

load_dotenv()


class Rename(Resource):

    def post(self):
        data = request.form.to_dict(flat=True)
        # check if file exists on s3
        # data = request.get_json()
        oldName = data['disk']+'/'+data['oldName']
        newName = data['disk']+'/'+data['newName']
        output = []
        try:
            # preper download command
            copyCommand = f"rclone move \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{oldName}\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{newName}\""
            with subprocess.Popen(copyCommand, shell=True, stdout=subprocess.PIPE) as copyProcess:
                (downOut, downErr) = copyProcess.communicate()
                # wait until download complete
            copyProcess.wait()

            return {"message": "file has been rename successfully.", "status": 200}, 200
        except:
            return {"message": "We couldn\'t extracting the giving directory .", "status": 400}, 200
