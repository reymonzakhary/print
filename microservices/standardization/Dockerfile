FROM python:3

LABEL MAINTAINER="Reymon Zakhary <reymon@chd.com.eg>"
# the root dir of the server
WORKDIR /var/www
# include all requirement for installations
COPY requirements.txt requirements.txt
# install requirements
RUN pip install -r requirements.txt

COPY app.py .

RUN python -m spacy download en_core_web_sm

EXPOSE 5000

CMD [ "python", "app.py"]