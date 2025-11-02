db.auth('admin', 'scret')
db = db.getSiblingDB('standardization')
db.createUser({
  user: 'admin',
  pwd: 'scret',
  roles: [
    {
      role: 'root',
      db: 'admin',
    },
  ],
});