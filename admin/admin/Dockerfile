# Here should use an appropriate LTS image instead of node:22-slim
FROM node:22-slim AS builder
WORKDIR /app
# Copy package files first (for better Docker caching)
COPY package*.json yarn.lock .yarnrc.yml ./
# Add Conig Set
RUN corepack enable && yarn set version stable

# Install dependencies (Yarn 4 uses --immutable instead of --frozen-lockfile)
RUN yarn install --immutable

#copy the rest of the project
COPY . .

# Increase Node.js heap size to prevent memory issues during build
ENV NODE_OPTIONS="--max-old-space-size=4096"

# Run build step with npx to ensure the correct version of Nuxt is used
RUN npx nuxt build

# Build project dependencies
#RUN yarn build

# Apply LTS image
FROM node:22-slim AS runner

# Add Additional Packages (without version pinning for compatibility)
RUN apt-get update && \
    apt-get install -y vim net-tools systemd gnutls-bin && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV NODE_ENV=production
ENV HOST=0.0.0.0

# copy over build files from builder step
COPY --from=builder /app/.output ./.output

# expose the image port
EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]