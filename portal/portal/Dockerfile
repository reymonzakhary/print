# Here should use an appropriate LTS image instead of node:22-slim
FROM node:22-slim AS builder
WORKDIR /app
# copy project files and folders to the current working directory (i.e. 'app' folder)
COPY package*.json .yarnrc.yml ./
# Add Conig Set
RUN corepack enable && yarn set version stable
# instaling project dependencies
RUN yarn install

#copy the rest of the project
COPY . .

# Copy the .env file
COPY .env .env

# Increase Node.js heap size to prevent memory issues during build
ENV NODE_OPTIONS="--max-old-space-size=4096"

# Build project dependencies
RUN yarn build

# Apply LTS image
FROM node:22-slim AS runner

# Add Additional Packages (without version pinning for compatibility)
RUN apt-get update && \
    apt-get install -y vim net-tools systemd gnutls-bin && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV NODE_ENV production
ENV HOST 0.0.0.0

# copy over build files from builder step
COPY --from=builder /app/.output ./.output

# expose the image port
EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]