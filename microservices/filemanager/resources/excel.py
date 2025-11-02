from flask import request
from flask_restful import Resource
import os
import json
from dotenv import load_dotenv
import pandas as pd
import subprocess
import shutil
import xlsxwriter
import os.path
from os import path

load_dotenv()

class ExcelRead(Resource):

    def post(self):
        data = request.form.to_dict(flat=True)
        # check if file exists on s3
        # data = request.get_json()
        path = "tenancy/"+ data['path']
        result = {}
        if os.environ.get('CONFIG') == 'Development':
            xls = pd.ExcelFile('storage/'+path)
            sheetNames = xls.sheet_names
            for sheetName in sheetNames:
                self.readsheet(path,result,sheetName)
            if(len(sheetNames) == 1):
                result = result[sheetNames[0]]
            return {"data":result , "status":200 },200
        else:
            dir = os.path.dirname(path)
            # download the directory to local

            downCommand = f"rclone copy \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{path}\" \"storage/{dir}\""

            with subprocess.Popen(downCommand, shell=True ,stdout=subprocess.PIPE) as downProcoss:
                (out, err) = downProcoss.communicate()
            downProcoss.wait()
            xls = pd.ExcelFile('storage/'+path)
            sheetNames = xls.sheet_names
            # read excel file from path
            for sheetName in sheetNames:
                self.readsheet(path,result,sheetName)
            if(len(sheetNames) == 1):
                result = result[sheetNames[0]]
            os.remove('storage/' + path)
            return {"data":result , "status":200 },200

    def readsheet(slef,path,result,sheetname=""):
        df = pd.read_excel('storage/'+path,sheet_name=sheetname)
        result[sheetname] = []
        for index,column in df.iterrows():
            result[sheetname].append( json.loads(column.to_json()) )

class ExportExcel(Resource):

    def post(self):
        # data = request.form.to_dict(flat=True)
        # check if file exists on s3
        data = request.get_json()
        path = "tenancy/"+data['path']
        filename = os.path.basename(path)

        path = ('/'.join(path.split("/")[:-1]))
        cols = data['cols']
        data = data['data']
        if not os.path.exists(os.path.dirname("storage/"+path)):
            # create directory
            os.makedirs('storage/'+path, exist_ok=True)

        excel = pd.DataFrame(data, columns=cols)
        excel.to_excel("storage/"+path + '/' + filename)
        if os.environ.get('CONFIG') != 'Development':
            uploadCommand = f"rclone copy \"storage/{path}\" \"spaces-ams3:{os.environ.get('S3_BUCKET')}/{path}\""
            with subprocess.Popen(uploadCommand, shell=True ,stdout=subprocess.PIPE) as uploadProcoss:
                (out, err) = uploadProcoss.communicate()
        os.chmod(os.path.dirname('storage/'+path), 0o777)
        # filename = os.path.basename("storage/"+path)
        filesize = os.path.getsize("storage/"+path)
        if os.environ.get('CONFIG') != 'Development':
            os.remove('storage/' + path + '/' + filename)
        return {"path":path,"name":filename,"size":filesize , "status":200 },200
