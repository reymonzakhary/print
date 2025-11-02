# 🔹 Use official Python image as base
FROM python:3.10

LABEL MAINTAINER="Reymon Zakhary <reymon@prindustry.com>"
# Tell Python to not generate .pyc
ENV PYTHONDONTWRITEBYTECODE 1
# Turn off buffering
ENV PYTHONUNBUFFERED 1

RUN apt-get update && \
    apt-get -y install vim netstat-nat
# 🔹 Set working directory inside container
WORKDIR /var/www/
#prepare directory for certificates
RUN mkdir -p /var/crt
# lint
RUN pip install --upgrade pip

# 🔹 Copy necessary files
COPY . /var/www/

# 🔹 Install dependencies
RUN pip install --no-cache-dir -r requirements.txt

#expose
EXPOSE 5000

# Start the application
CMD ["python", "app.py"]