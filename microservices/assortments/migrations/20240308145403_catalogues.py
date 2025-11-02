from mongodb_migrations.base import BaseMigration
from displayName import display_name

class Migration(BaseMigration):
    def upgrade(self):
        for item in self.db.catalogues.find():
            item['material'] = item['material']
            item['grs'] = item['grs']
            item['additional'] = {}
            self.db.catalogues.save(item)

    def downgrade(self):
        for item in self.db.catalogues.find():
            del item['material']
            del item['grs']
            del item['additional']
            self.db.catalogues.save(item)
