FROM node:14

RUN apt-get update && \
    apt-get install -y vim net-tools

#prepare directory for certificates
RUN mkdir -p /var/crt

# Create app directory
WORKDIR /app

# Install app dependencies
# A wildcard is used to ensure both package.json AND package-lock.json are copied
# where available (npm@5+)
COPY package*.json ./

RUN yarn
# If you are building your code for production
# RUN yarn ci --only=production

# Bundle app source
COPY . .

RUN cp -r .env.example .env

EXPOSE 3333
CMD [ "node", "server.js" ]
