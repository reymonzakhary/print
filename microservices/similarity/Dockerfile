FROM python:3.9.7

LABEL MAINTAINER="Reymon Zakhary <reymon@chd.com.eg>"
# the root dir of the server
WORKDIR /var/www
# include all requirement for installations
COPY requirements.txt requirements.txt
# install requirements
RUN pip install -r requirements.txt
#RUN python -m spacy download en_core_web_md
COPY app.py .

EXPOSE 5000

CMD [ "python", "app.py"]