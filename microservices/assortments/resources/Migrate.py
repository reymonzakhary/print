import os
from flask_restful import Resource, fields, marshal


class MigrateAPI(Resource):
    def get(self, status):
        config = {
            "url": "mongodb://{}/{}".format(os.getenv('HOST'), os.getenv('DB')),
            "username": "{}".format(os.getenv('USERNAME')),
            "password": "{}".format(os.getenv('PASSWORD')),
            "database": "{}".format(os.getenv('DB')),
            "migrations": "migrations",
            "metastore": "database_migrations",
            "status": "" if status == "up" else "--downgrade",
            "host": "mongodb+srv://doadmin:fCMt8796jW251eF0@private-db-mongodb-ams3-cec-dev-7aa88438.mongo.ondigitalocean.com/admin?replicaSet=db-mongodb-ams3-cec-dev&authSource=admin&&tlsCAFile=./ca-certificate.crt"
        }

        if os.getenv('APP_ENV') == "development":
            cmd = "mongodb-migrate {status} --url {url} --username {username} --password {password} --database {" \
                  "database} --migrations {migrations} --metastore {metastore} ".format(
                **config)
        else:
            cmd = "mongodb-migrate --url {host} --migrations {migrations} --metastore {metastore}".format(**config)

        os.system(cmd)
        return "Migrate upgrade" if status == "up" else "Migrate downgrade"
