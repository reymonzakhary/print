from mongodb_migrations.base import BaseMigration
from displayName import display_name


class Migration(BaseMigration):
    def upgrade(self):
        for item in self.db.supplier_options.find():
            item['system_key'] = item['name']
            item['display_name'] = display_name(item)
            item['additional'] = {}
            self.db.supplier_options.save(item)

    def downgrade(self):
        for item in self.db.supplier_options.find():
            if "display_name" in item:
                del item['display_name']
                item['display_name'] = item['name']
            if "system_key" in item:
                del item['system_key']
            if "additional" in item:
                del item['additional']
            self.db.supplier_options.save(item)
