from mongodb_migrations.base import BaseMigration
from displayName import display_name


class Migration(BaseMigration):
    def upgrade(self):
        for item in self.db.boxes.find():
            item['system_key'] = item['name']
            item['display_name'] = display_name(item)
            item['additional'] = {}
            self.db.boxes.save(item)

    def downgrade(self):
        for item in self.db.boxes.find():
            if "display_name" in item:
                del item['display_name']
                item['display_name'] = item['name']
            del item['system_key']
            del item['additional']
            self.db.boxes.save(item)
