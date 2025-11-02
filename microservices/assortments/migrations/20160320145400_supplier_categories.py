from mongodb_migrations.base import BaseMigration
from displayName import display_name

class Migration(BaseMigration):
    def upgrade(self):
        for item in self.db.supplier_categories.find():
            item['system_key'] = item['name']
            item['display_name'] = display_name(item)
            item['additional'] = {}
            item['production_days'] = [
                {
                    "day": "mon",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "tue",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "wed",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "thu",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "fri",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "sat",
                    "active": True,
                    "deliver_before": "12:00"
                },
                {
                    "day": "sun",
                    "active": False,
                    "deliver_before": "12:00"
                }]

            self.db.supplier_categories.save(item)

    def downgrade(self):
        for item in self.db.supplier_categories.find():
            if "display_name" in item:
                del item['display_name']
                item['display_name'] = item['name']
            if 'system_key' in item:
                del item['system_key']
            if 'additional' in item:
                del item['additional']
            if 'production_days' in item:
                del item['production_days']
            self.db.supplier_categories.save(item)
