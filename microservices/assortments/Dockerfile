FROM python:3.9.7

LABEL MAINTAINER="Reymon Zakhary <reymon@prindustry.com>"
# Tell Python to not generate .pyc
ENV PYTHONDONTWRITEBYTECODE 1
# Turn off buffering
ENV PYTHONUNBUFFERED 1

RUN apt-get update && \
    apt-get -y install vim netstat-nat
# the root dir of the server
WORKDIR /var/www/
#prepare directory for certificates
RUN mkdir -p /var/crt
# lint
RUN pip install --upgrade pip
#RUN pip install flake8
COPY . /var/www/

RUN cp -r .env_example .env

#install all dependencies
RUN pip install -r requirements.txt
#expose
EXPOSE 5000

CMD ["python", "app.py"]