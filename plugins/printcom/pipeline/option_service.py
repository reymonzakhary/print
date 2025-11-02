import itertools
import json
from datetime import datetime
from config import DB_NAME, OPTION_COLL, BOOBS_COLL, POLICY_COLL
from pipeline.policy_service import PolicyService


class OptionService:
    def __init__(self, client):
        """Initialize OptionService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.opts_collection = self.db[OPTION_COLL]
        self.oops_collection = self.db[BOOBS_COLL]
        self.policies_collection = self.db[POLICY_COLL]
        self.policy_service = PolicyService(client)

    def modify_option(self, product_sku, property_slug, option_data, product_id, property_id, tenant_name, tenant_id,
                      property_data):
        """Modify option data before inserting into MongoDB."""
        if product_sku == "copies":
            return None

        slug = option_data.get("slug", "unknown_option")
        name = str(option_data.get("name", slug))
        title = str(option_data.get("title", name))
        required = option_data.get("nullable", False)
        slug_or_name = slug if slug else name

        dynamic_type = None
        if option_data.get("customSizes", False):
            dynamic_type = 'format'

        return {
            "product_sku": product_sku,
            "property_slug": property_slug,
            "sku": product_sku,
            "slug": slug_or_name,
            "system_key": slug_or_name,
            "name": title,
            "nullable": required,
            "eco": option_data.get("eco", False) if "eco" in option_data else None,
            "customSizes": option_data.get("customSizes", None),
            "created_at": datetime.utcnow().isoformat(),
            "input": option_data,
            "sort": 0,
            "rpm": 0,
            "information": "",
            "additional": {"test": 0},
            "sheet_runs": [],
            "parent": True,
            "has_children": False,
            "dynamic": dynamic_type is not None,
            "shareable": True,
            "extended_fields": {},
            "calculation_method": "external",
            "input_type": "radio",
            "dimension": "2d",
            "tenant_name": tenant_name,
            "tenant_id": tenant_id,
            "media": [],
            "dynamic_keys": [],
            "generate": False,
            "dynamic_type": dynamic_type,
            "width": option_data.get("customSizes", {}).get("minWidth", 0),
            "maximum_width": option_data.get("customSizes", {}).get("maxWidth", 0),
            "minimum_width": option_data.get("customSizes", {}).get("minWidth", 0),
            "height": option_data.get("customSizes", {}).get("maxHeight", 0),
            "maximum_height": option_data.get("customSizes", {}).get("minHeight", 0),
            "minimum_height": option_data.get("customSizes", {}).get("maxHeight", 0),
            "length": 0,
            "maximum_length": 0,
            "minimum_length": 0,
            "unit": '',
            "incremental_by": 0,
            "start_on": 0,
            "end_on": 0,
            "published": True,
            "display_name": [
                {"iso": 'en', "display_name": title if title else name},
                {"iso": 'fr', "display_name": title if title else name},
                {"iso": 'nl', "display_name": title if title else name},
                {"iso": 'de', "display_name": title if title else name},
            ],
            "product_id": [product_id],
            "property_id": [property_id],
            "excludes": [],
            "includes": [],
        }

    def store_options_in_mongo(self, product_sku, property_slug, options, excludes, includes, product_id, property_id,
                               tenant_name, tenant_id, property_data):
        """Store options in the 'opts' collection."""
        if not options:
            return

        for option in options:
            modified_option = self.modify_option(product_sku, property_slug, option, product_id, property_id,
                                                 tenant_name, tenant_id, property_data)

            if modified_option:
                self.opts_collection.update_one(
                    {"product_sku": modified_option["product_sku"], "property_slug": modified_option["property_slug"],
                     "slug": modified_option["slug"]},
                    {"$set": modified_option},
                    upsert=True
                )

    def get_option_ids_from_oops(self):
        """Retrieve option IDs from 'oops' collection and update exclusions."""
        oops_documents = list(self.oops_collection.find())
        option_ids = []

        for doc in oops_documents:
            if 'boops' in doc:
                for boop in doc['boops']:
                    for ops in boop['ops']:
                        if '_id' in ops:
                            product_sku = str(ops["product_sku"])
                            property_slug = str(ops["property_slug"])
                            excludes = self.policy_service.get_other_excluded_options(product_sku, str(ops["_id"]))

                            if 'excludes' in ops:
                                # ex_list = [[ex] for ex in excludes if str(ops["_id"]) != ex]
                                ex_list = [ex for ex in excludes if str(ops["_id"]) != ex]
                                ops['excludes'] = [ex_list]

                                # composite_id = self.generate_composite_id(property_slug)
                                # ops['composite_id'] = composite_id

                            option_ids.append(str(ops['_id']))

                    self.oops_collection.update_one(
                        {"_id": doc["_id"]},
                        {"$set": {"boops": doc["boops"]}}
                    )

        return option_ids

    def generate_composite_id(self, property_slug):
        """
        Generate a composite ID based on the property_slug.
        The composite ID will be an array of the property_slug for this ops.
        """
        return [property_slug]

    def update_excludes_in_ops(self):
        """
        Ensures `excludes` are correctly reflected between `boops` collections:
        - If an `ops._id` appears in `excludes`, its corresponding `ops` in another `boops` should also exclude it.
        - Changes only apply across `boops`, NOT within the same `boops`.
        - Keeps `excludes` formatted correctly as lists of lists.
        """

        # Fetch all documents
        documents = self.oops_collection.find()

        for document in documents:
            if not document or "boops" not in document:
                print(f"No matching document found")
                continue  # Skip if no boops

            document_id = str(document["_id"])

            boops_ops_map = {}
            for boop_index, boop in enumerate(document["boops"]):
                boops_ops_map[boop_index] = {}
                for ops in boop.get("ops", []):
                    if "_id" in ops and "property_slug" in ops:
                        boops_ops_map[boop_index][str(ops["_id"])] = ops["property_slug"]

            for boop_index, boop in enumerate(document["boops"]):
                for ops_index, ops in enumerate(boop.get("ops", [])):
                    if "_id" not in ops or "excludes" not in ops:
                        continue

                    ops_id = str(ops["_id"])
                    property_slug = ops["property_slug"]
                    updated_excludes = [exclude_list for exclude_list in ops["excludes"] if
                                        exclude_list]  # Remove empty lists

                    for exclude_list in ops["excludes"]:
                        for excluded_id in exclude_list:
                            for other_boops_index, other_boops_ops in boops_ops_map.items():
                                if other_boops_index == boop_index:
                                    continue

                                if excluded_id in other_boops_ops:
                                    other_property_slug = other_boops_ops[excluded_id]

                                    for target_ops_index, target_ops in enumerate(
                                            document["boops"][other_boops_index].get("ops", [])):
                                        if str(target_ops["_id"]) == excluded_id and target_ops[
                                            "property_slug"] == other_property_slug:
                                            # Ensure it doesn't already exist in excludes
                                            target_updated_excludes = [
                                                sublist for sublist in target_ops["excludes"] if sublist
                                            ]
                                            if not any(ops_id in sublist for sublist in target_updated_excludes):
                                                target_updated_excludes.append([ops_id])

                                            update_query = {
                                                f"boops.{other_boops_index}.ops.{target_ops_index}.excludes": target_updated_excludes
                                            }
                                            self.oops_collection.update_one(
                                                {"_id": document["_id"]}, {"$set": update_query}
                                            )

        return {"message": "Updated `excludes` correctly between `boops`"}

    def generate_combinations_for_non_multi(self):
        """
        Generate a dictionary of combinations for policies where 'is_multi' is false.
        This function checks the `composite_id` length and processes only those with 2 properties in `composite_id`.
        Each combination will be wrapped in a separate list, ensuring that duplicates are not added.
        """
        # Step 1: Fetch the policies based on product_slug
        policies = list(self.policies_collection.find())

        if not policies:
            print(f"No policies found for product_slug")
            return {}

        # Step 2: Initialize result dictionary
        result = {}

        # Step 3: Iterate through each policy
        for policy in policies:
            excludes = policy.get("excludes", [])
            product_slug = policy.get("product_slug", None)

            # Step 4: Iterate through each exclusion list in the policy
            for exclude_list in excludes:
                if isinstance(exclude_list, list):
                    print(f"Processing exclusion list for policy {policy['_id']}...")  # Debugging

                    # Step 5: Store the properties and their options dynamically
                    property_options = {}  # Store property and its options
                    for exclude_dict in exclude_list:
                        if isinstance(exclude_dict, dict):
                            # Check if the 'property' key exists before accessing it
                            if "property" in exclude_dict and "options" in exclude_dict:
                                property_slug = exclude_dict["property"]
                                options = exclude_dict["options"]
                                property_options[property_slug] = options  # Store options by property
                            else:
                                print(f"Skipping exclude_dict: {exclude_dict}. Missing 'property' or 'options'.")
                                continue

                    # Step 6: Generate combinations for only `is_multi = false` and ensure composite_id length is 2
                    for exclude_dict in exclude_list:
                        if "composite_id" in exclude_dict and len(exclude_dict["composite_id"]) == 2:
                            properties = exclude_dict["composite_id"]

                            # Loop through each option in the properties and generate combinations
                            for property_slug, options in property_options.items():
                                if property_slug in properties:  # Ensure it's part of the composite_id
                                    for option in options:
                                        if option not in result:
                                            result[option] = []

                                        # Loop through the other property options to create the exclusions
                                        for other_property_slug, other_options in property_options.items():
                                            if property_slug != other_property_slug:
                                                # Add exclusions for the current option from the other property
                                                for other_option in other_options:
                                                    # Ensure the other_option is not already in the list
                                                    if [other_option] not in result[option]:
                                                        result[option].append(
                                                            [other_option])  # Wrap each option in its own array

                    print(f"Generated Combinations for Policy {policy['_id']}: {result}")  # Debugging

            # Step 7: Call update_boops_redundant to update the 'boops' collection after generating combinations
            for option, exclusions in result.items():
                # For each option, fetch the corresponding ops_id and update the exclusions
                self.update_boops_redundant(product_slug, option, exclusions)

        return result

    def generate_combinations_for_multi(self):
        """
        Generate a dictionary of combinations for policies where 'is_multi' is true.
        This function checks the `composite_id` length and processes only greater than 2 properties in `composite_id`.
        """
        # Step 1: Fetch the policies based on product_slug
        # policies = list(self.policies_collection.find({"product_slug": product_slug}))
        policies = list(self.policies_collection.find())

        if not policies:
            print(f"No policies found for product_slug")
            return {}

        # Step 2: Initialize result dictionary
        result = {}

        # Step 3: Iterate through each policy
        for policy in policies:
            excludes = policy.get("excludes", [])
            product_slug = policy.get("product_slug", None)

            # Step 4: Iterate through each exclusion list in the policy
            for exclude_list in excludes:
                if isinstance(exclude_list, list):
                    print(f"Processing exclusion list for policy {policy['_id']}...")  # Debugging

                    # Step 5: Store the properties and their options dynamically
                    property_options = {}  # Store property and its options
                    for exclude_dict in exclude_list:
                        if isinstance(exclude_dict, dict):
                            # Check if the 'property' key exists before accessing it
                            if "property" in exclude_dict and "options" in exclude_dict:
                                property_slug = exclude_dict["property"]
                                options = exclude_dict["options"]
                                property_options[property_slug] = options  # Store options by property
                            else:
                                print(f"Skipping exclude_dict: {exclude_dict}. Missing 'property' or 'options'.")
                                continue

                    # Step 6: Generate combinations for only `is_multi = true` and ensure composite_id length is greater than 2
                    for exclude_dict in exclude_list:
                        if "composite_id" in exclude_dict and len(exclude_dict["composite_id"]) > 2:
                            properties = exclude_dict["composite_id"]

                            # Loop through each option in the properties and generate combinations
                            for property_slug, options in property_options.items():
                                if property_slug in properties:  # Ensure it's part of the composite_id
                                    for option in options:
                                        if option not in result:
                                            result[option] = []

                                        # Loop through the other property options to create the exclusions
                                        for other_property_slug, other_options in property_options.items():
                                            if property_slug != other_property_slug:
                                                # Add exclusions for the current option from the other property
                                                if other_options:
                                                    result[option].append(other_options)
            try:
                combination_result = self.excludes_ids_combination(result)

                print(combination_result)
                # Step 7: Call update_boops_redundant to update the 'boops' collection with the modified exclusions
                for option, exclusions in combination_result.items():
                    self.update_boops_redundant(product_slug, option, exclusions)
            except Exception as e:
                print(f"mess {e}")

        return result

    def excludes_ids_combination(self, data):
        """
        Generate a dictionary of combinations for policies where 'is_multi' is true.
        This function combines each item in the nested arrays (lists) for each key dynamically.
        """
        combined_result = {}

        for key, lists_to_combine in data.items():
            # Using a generator instead of a list to save memory
            combined_result[key] = (list(combination) for combination in itertools.product(*lists_to_combine))

        return combined_result  # Returns a dictionary with generators instead of lists

    def update_boops_redundant(self, product_slug, ops_id, exclusions):
        """
        Update the 'excludes' in 'boops' collection for a given ops_id.
        The exclusions are added to the relevant ops object identified by ops_id.

        :param product_slug: The SKU or product identifier
        :param ops_id: The ID of the ops to update
        :param exclusions: A list of exclusions to add to the ops
        """
        # Step 1: Fetch the document from 'oops' collection based on the product_slug
        oops_document = self.oops_collection.find_one({"sku": product_slug})

        if not oops_document:
            print(f"No document found for product_slug: {product_slug}")
            return

        # Step 2: Iterate through boops to find the matching ops_id
        for boop in oops_document['boops']:
            for ops in boop['ops']:
                if str(ops['_id']) == str(ops_id):
                    print(f"Found ops with ID {ops_id}. Updating exclusions...")  # Debugging

                    # Step 3: Add exclusions to the found ops object
                    if "excludes" not in ops:
                        ops["excludes"] = []

                    # Step 4: Add new exclusions, ensuring no duplicates
                    for exclusion in exclusions:
                        if exclusion not in ops["excludes"]:
                            ops["excludes"].append(exclusion)

                    # Step 5: Update the document in the 'oops' collection with the modified exclusions
                    self.oops_collection.update_one(
                        {"_id": oops_document["_id"], "boops.ops._id": ops["_id"]},
                        {"$set": {"boops.$.ops": boop['ops']}}
                    )

                    return  # Exit after updating the first match

        return f"ops_id {ops_id} not found in product_slug {product_slug}."

    def generate_combinations(self, product_slug):
        """
        Generate a dictionary of combinations for policies where 'is_multi' is true or false.
        This function checks the `composite_id` length and processes the properties accordingly.
        """
        # Step 1: Fetch the policies based on product_slug
        policies = list(self.policies_collection.find({"product_slug": product_slug}))

        if not policies:
            print(f"No policies found for product_slug: {product_slug}")
            return {}

        # Step 2: Initialize result dictionary
        result = {}

        # Step 3: Iterate through each policy
        for policy in policies:
            excludes = policy.get("excludes", [])

            # Step 4: Iterate through each exclusion list in the policy
            for exclude_list in excludes:
                if isinstance(exclude_list, list):
                    print(f"Processing exclusion list for policy {policy['_id']}...")  # Debugging

                    # Step 5: Store the properties and their options dynamically
                    property_options = {}  # Store property and its options
                    for exclude_dict in exclude_list:
                        if isinstance(exclude_dict, dict):
                            # Check if the 'property' key exists before accessing it
                            if "property" in exclude_dict and "options" in exclude_dict:
                                property_slug = exclude_dict["property"]
                                options = exclude_dict["options"]
                                property_options[property_slug] = options  # Store options by property
                            else:
                                print(f"Skipping exclude_dict: {exclude_dict}. Missing 'property' or 'options'.")
                                continue

                    # Step 6: Check if `is_multi` is true or false and generate combinations accordingly
                    for exclude_dict in exclude_list:
                        if "composite_id" in exclude_dict:
                            properties = exclude_dict["composite_id"]
                            is_multi = exclude_dict.get("is_multi", False)

                            # If `is_multi` is false, wrap each exclusion in a separate array
                            if is_multi is False and len(properties) == 2:
                                for property_slug, options in property_options.items():
                                    if property_slug in properties:
                                        for option in options:
                                            if option not in result:
                                                result[option] = []

                                            for other_property_slug, other_options in property_options.items():
                                                if property_slug != other_property_slug:
                                                    # Wrap each option in its own array
                                                    for other_option in other_options:
                                                        if [other_option] not in result[option]:
                                                            result[option].append([other_option])

                            # If `is_multi` is true, just generate the combinations directly
                            elif is_multi is True:
                                for property_slug, options in property_options.items():
                                    if property_slug in properties:
                                        for option in options:
                                            if option not in result:
                                                result[option] = []

                                            for other_property_slug, other_options in property_options.items():
                                                if property_slug != other_property_slug:
                                                    for other_option in other_options:
                                                        result[option].append(other_option)

        # Step 7: Return the combined result
        return result
