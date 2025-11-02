<p align="center">
  <img src="https://github.com/prindustry/admin/blob/main/admin/app/assets/images/Prindustry-box.png?raw=true" alt="Prindustry Logo" width="100"/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/node-v22-brightgreen.svg" alt="Node Version" />
  <img src="https://img.shields.io/badge/nuxt-3.15.4-00DC82.svg" alt="Nuxt Version" />
  <img src="https://img.shields.io/badge/yarn-4.6.0-2C8EBB.svg" alt="Yarn Version" />
</p>

## Overview

The Prindustry Admin Panel is a management interface designed for Prindustry employees to efficiently create and manage tenants within the CEC multi-tenancy system. This application provides a user-friendly interface for tenant administration.

## Installation

> [!IMPORTANT]
> At the time of writing, the dev branch of CEC is not yet compatible with this project.
> Please make sure to use the `CE1-T437` branch of CEC for this project.

> [!NOTE]
> Please be amicable and update the cec-api-v2-endpoints.json file with API-changes from CEC. This way we can keep the API-documentation up to date for each other. Shukran :)

### Prerequisites

- Node.js v22
- Yarn 4.6.0
- Docker (for CEC)

### Setup Instructions

1. **Node Version**

   ```bash
   # Verify your Node version
   node --version # Should be v22
   # If not, install Node v22.0.0
   nvm install 22
   nvm use 22
   ```

2. **Environment Configuration**

   ```bash
   # Create .env file
   cp .env.example .env
   ```

3. **Yarn Configuration**
   Create a `.yarnrc.yml` file with the following content:

   ```yaml
   npmScopes:
     fortawesome:
       npmAuthToken: "YOUR_FONTAWESOME_PRO_TOKEN"
       npmRegistryServer: "https://npm.fontawesome.com/"
   ```

4. **Install Dependencies**

   ```bash
   yarn install
   ```

5. **Make sure prindustry.test is pointing to the correct IP address**

   ```bash
   # Edit /etc/hosts file
   sudo nano /etc/hosts
   # Add the following line
   127.0.0.1 manager.prindustry.test
   ```

6. **Start Development Server**

   ```bash
   # Ensure CEC Docker is running first
   docker ps # Verify CEC containers are active

   # Start the development server
   yarn dev
   ```

The application will be available at `http://manager.prindustry.test:3000`

## Development

### Nuxt

For detailed explanation on how things work, check out [Nuxt.js docs](https://nuxt.com).

### CEC

For a working example of how to use the CEC API V2, import the 'cec-api-v2-endpoints.json' file into Insomnia and change the `/auth/login` endpoint payload to your credentials.

For detailed explanation on how things work, check out [CEC docs](https://github.com/prindustry/cec).

## License

© 2024 Prindustry. All rights reserved.
