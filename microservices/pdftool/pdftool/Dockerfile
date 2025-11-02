FROM python:3.9.7

LABEL MAINTAINER="Reymon Zakhary <reymon@prindustry.com>"
# Tell Python to not generate .pyc
ENV PYTHONDONTWRITEBYTECODE 1
# Turn off buffering
ENV PYTHONUNBUFFERED 1

RUN apt-get update && \
    apt-get -y install vim netstat-nat ghostscript

# the root dir of the server
WORKDIR /var/www/
RUN curl https://rclone.org/install.sh | bash

#prepare directory for certificates

RUN mkdir -p /var/crt
RUN mkdir -p storage
RUN mkdir -p ~/.config/rclone
RUN touch ~/.config/rclone/rclone.conf
COPY rclone.conf ~/.config/rclone/rclone.conf

RUN chmod 600 ~/.config/rclone/rclone.conf

#RUN addgroup --system -gid 1000 app && adduser --system -uid 1000 -gid 1000 app
#RUN chown -R app /var/www/
#USER app
#COPY --chown=1000:root . /var/www/

# lint
COPY . /var/www/
RUN pip install --upgrade pip
#RUN pip install flake8
RUN cp -r .env_example .env
#install all dependencies
RUN pip install -r requirements.txt

COPY ./policy.xml /etc/ImageMagick-6/policy.xml


#expose
EXPOSE 5000

CMD ["python", "app.py"]
