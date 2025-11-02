FROM node:22

# Working directory
WORKDIR /var/www

#prepare directory for certificates
RUN mkdir -p /var/crt

#copy the package file to the working container
COPY . .

#install the package
RUN yarn

EXPOSE 3333
#default command line
CMD [ "yarn","prod"]
