from flask import Flask, request, jsonify, Response,send_file

from boto3 import client,resource
from os import environ, path
from dotenv import load_dotenv

basedir = path.abspath(path.dirname(__file__))
# load_dotenv(path.join(basedir, '.env'))

def get_client():
    return client(
        's3',
        region_name=environ.get('S3_REGION_NAME',"ams3"),
        endpoint_url=environ.get('S3_ENDPOINT_URL',"https://ams3.digitaloceanspaces.com"),
        aws_access_key_id=environ.get('S3_ACCESS_ID','7ZGKHBRJ3HICZQXAUMR4'),
        aws_secret_access_key=environ.get('S3_SECRET_KEY','TgoCc5fWwh+vxyzLkGWAPHZ20mLtknboyW8I5X6nJxU')
    )
    
def download_file(file_name):
    """
    Function to download a given file from an S3 bucket
    """
    s3 = resource('s3')
    output = f"Storage/{file_name}"
    s3.Bucket(environ.get('S3_BUCKET',"cec-ams3-prod")).download_file(file_name, output)

    return output

def getFile(path):
    s3 = get_client()
    file = s3.get_object(Bucket=environ.get('S3_BUCKET',"cec-ams3-prod"), Key=path)
    return Response(
        file['Body'].read(),
        mimetype='zip/plain',
        headers={"Content-Disposition": "attachment;filename="+path}
    )

def get_object():
    s3 = get_client()
    return s3.get_object(Bucket=environ.get('S3_BUCKET',"cec-ams3-prod"), Key='blah.txt')['Body'].read()