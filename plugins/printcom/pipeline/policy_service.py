from config import DB_NAME, POLICY_COLL, OPTION_COLL, CATEGORY_COLL, BOOBS_COLL, BOX_COLL

class PolicyService:
    def __init__(self, client):
        """Initialize PolicyService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.policies_collection = self.db[POLICY_COLL]
        self.options_collection = self.db[OPTION_COLL]

    # def sync_excludes(self, product_slug):
    #     """
    #     Sync excludes for a specific product slug where 'synced' is false.
    #
    #     :param product_slug: The product slug to filter by
    #     """
    #     policies = list(self.policies_collection.find({"product_slug": product_slug, "synced": False}))
    #
    #     for policy in policies:
    #         excludes = policy.get("excludes", [])
    #
    #         for exclude_list in excludes:
    #             if isinstance(exclude_list, list):
    #                 for exclude_dict in exclude_list:
    #                     if isinstance(exclude_dict, dict) and "options" in exclude_dict:
    #                         option_slugs = exclude_dict["options"]
    #                         property_slug = exclude_dict["property"]
    #
    #
    #                         # Fetch corresponding option IDs from options_collection
    #                         options = list(self.options_collection.find({
    #                             "$and": [
    #                                 {"slug": {"$in": option_slugs}},
    #                                 {"product_sku": product_slug},
    #                                 {"property_slug": property_slug}
    #                             ]
    #                         }))
    #                         updated_options = [str(option["_id"]) for option in options]
    #
    #                         # Replace 'options' field with the updated IDs
    #                         exclude_dict["options"] = updated_options
    #
    #         # Add 'is_multi' flag to the exclude_list
    #         for exclude_list in excludes:
    #             if isinstance(exclude_list, list):
    #                 # Set 'is_multi' flag at the end of the exclude_list
    #                 is_multi = len(exclude_list) > 2
    #                 exclude_list.append({"is_multi": is_multi})
    #
    #
    #         # Update the policy in the database
    #         self.policies_collection.update_one(
    #             {"_id": policy["_id"]},
    #             {"$set": {"excludes": excludes, "synced": True}}
    #         )

    def sync_excludes(self, product_slug):
        """
        Sync excludes for a specific product slug where 'synced' is false.

        :param product_slug: The product slug to filter by
        """
        policies = list(self.policies_collection.find({"product_slug": product_slug, "synced": False}))

        for policy in policies:
            excludes = policy.get("excludes", [])

            # Iterate through each exclusion list and handle the composite ID and is_multi flag
            for exclude_list in excludes:
                if isinstance(exclude_list, list):
                    # Track all the properties for the composite ID
                    property_list = []


                    # Iterate through the exclusion items
                    for exclude_dict in exclude_list:
                        if isinstance(exclude_dict, dict) and "options" in exclude_dict:
                            # Fetch options and property
                            options = exclude_dict["options"]
                            property_slug = exclude_dict["property"]

                            # Add the property to the property list for composite ID
                            property_list.append(property_slug)

                            # Fetch corresponding option IDs from options_collection
                            options_docs = list(self.options_collection.find({
                                "$and": [
                                    {"slug": {"$in": options}},
                                    {"product_sku": product_slug},
                                    {"property_slug": property_slug}
                                ]
                            }))
                            updated_options = [str(option["_id"]) for option in options_docs]

                            # Replace 'options' field with the updated option IDs
                            exclude_dict["options"] = updated_options

                    # Generate composite_id based on the property list
                    composite_id = self.generate_composite_id(property_list)

                    # Append the composite_id to the exclusion list
                    exclude_list.append({"composite_id": composite_id})

                    # Set 'is_multi' flag at the end of the exclude_list
                    is_multi = len(property_list) > 2  # Check if there are more than 2 exclusions
                    exclude_list.append({"is_multi": is_multi})

            # Update the policy in the database with the modified exclusions
            self.policies_collection.update_one(
                {"_id": policy["_id"]},
                {"$set": {"excludes": excludes, "synced": True}}
            )

    def generate_composite_id(self, property_list):
        """
        Generate a composite ID by combining the properties involved in the exclusion.
        The composite ID will be an array of the properties.
        """
        return property_list

    def get_other_excluded_options(self, product_slug, option_id):
        """
        Retrieve all other 'options' from 'excludes' within the same object
        where 'option_id' exists, excluding only the dictionary inside that object
        that contains 'option_id'. If 'option_id' is not in any dictionary in the object,
        skip the entire object.

        :param product_slug: The product slug to filter by
        :param option_id: The option ID to search for in 'options'
        :return: A list of unique 'options' excluding ONLY the dictionary containing option_id
        """
        policy = self.policies_collection.find_one({"product_slug": product_slug})

        if not policy:
            return []  # Return empty list if no matching document is found

        excludes = policy.get("excludes", [])
        all_other_options = set()  # Use a set to avoid duplicates

        for exclude_list in excludes:
            if isinstance(exclude_list, list):
                matched = False  # Flag to check if the object contains option_id
                temp_options = set()  # Store options for this object

                for exclude_dict in exclude_list:
                    if isinstance(exclude_dict, dict) and "options" in exclude_dict:
                        if option_id in exclude_dict["options"]:
                            matched = True  # Mark that we found option_id in this object
                            continue  # Skip this dictionary only

                        temp_options.update(exclude_dict["options"])  # Collect valid options

                # Only add options if the object contained option_id at least once
                if matched:
                    all_other_options.update(temp_options)

        return list(all_other_options)  # Convert set back to list before returning

    def delete_all_data(self):
        collections_to_clear = [
            BOOBS_COLL,
            CATEGORY_COLL,
            BOX_COLL,
            OPTION_COLL,
            POLICY_COLL,
        ]
        """
        Delete all data from relevant collections in the database.
        """

        for collection_name in collections_to_clear:
            collection = self.db[collection_name]
            result = collection.delete_many({})  # Delete all documents in the collection
            print(f"Deleted {result.deleted_count} documents from {collection_name} collection.")
