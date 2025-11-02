db.auth('admin', 'ad')
db = db.getSiblingDB('assortments')
db.createUser({
  user: 'admin',
  pwd: 'ad',
  roles: [
    {
      role: 'root',
      db: 'admin',
    },
  ],
});