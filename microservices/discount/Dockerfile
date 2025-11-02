FROM node:14

RUN apt-get update && \
    apt-get install -y --no-install-recommends apt-utils vim net-tools

#prepare directory for certificates
RUN mkdir -p /var/crt

# Create app directory
WORKDIR /app

#install adonis CLI
RUN npm i -g @adonisjs/cli

# Install app dependencies
# A wildcard is used to ensure both package.json AND package-lock.json are copied
# where available (npm@5+)
COPY package*.json ./

RUN npm install
# If you are building your code for production
RUN npm ci --only=production

# Bundle app source
COPY . .

RUN cp -r .env.example .env

EXPOSE 3333
#CMD ["adonis", "serve"]
CMD [ "node", "server.js" ]
