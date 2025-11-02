## AdonisJs API
Margins Service.

You can use this api to get margins service.

## Packagis in use
-[lucid-mongo](https://registry.npmjs.org/lucid-mongo/-/lucid-mongo-3.1.6.tgz).
-[mongodb-core](https://registry.npmjs.org/mongodb-core/-/mongodb-core-3.2.7.tgz).

## API endpoints
Domain    |  Method |   URI    |   Headers
------------    |   -------------    |   ------------   |   ------------
------------ | GET |  margins/{supplier_id}/{reseller_id}    |  Content-Type
------------ | POST |  margins/    |  Content-Type
------------ | PATCH |  margins/{id}    |  Content-Type
------------ | GET |  margins/prices/{supplier_id}/{reseller_id}/{category_id}/{collection}    |  Content-Type

## Usage:
-   npm i   

-   adonis seed 

-   adonis serve --dev    

**(Must have mongo db environment on your machine)**.

## Documentation
[Documentation](https://documenter.getpostman.com/view/4027544/SWTD8xMN).