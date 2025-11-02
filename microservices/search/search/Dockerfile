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

#install all dependencies
RUN pip install --no-cache-dir -r requirements.txt

#RUN python scripts/setup_redis.py && python scripts/load_data_set.py && python scripts/generate_data.py
#expose
EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]

