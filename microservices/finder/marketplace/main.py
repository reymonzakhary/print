import os

from fastapi import FastAPI
from motor.motor_asyncio import AsyncIOMotorClient
from collections import defaultdict
from typing import List, Optional, Dict
from bson import ObjectId, DBRef
from pydantic import BaseModel
from dotenv import load_dotenv
from bson.errors import InvalidId
from fastapi import HTTPException
app = FastAPI()
load_dotenv(override=True)

MONGO_URI = os.getenv("MONGO_URI", "mongodb://admin:ad@assortmentDB:27017")
DATABASE_NAME = os.getenv("DATABASE_NAME", "admin")

client = AsyncIOMotorClient(MONGO_URI)
db = client[DATABASE_NAME]
boobs_collection = db["supplier_boops"]
categories_collection = db["categories"]
manifest_boobs_collection = db["manifest_boobs"]

class GenerateBoobsService:
    MERGE_WITHOUT_LINKED = True

    @staticmethod
    def preserve_linked_as_data(doc: dict) -> dict:
        if "linked" in doc:
            doc["linked_data"] = str(doc["linked"])
            doc["linked_data2"] = str(doc["linked"])

        for boop in doc.get("boops", []):
            if "linked" in boop:
                boop["linked_data"] = str(boop["linked"])
                boop["linked"] = str(boop["linked"])

            for op in boop.get("ops", []):
                if "linked" in op:
                    op["linked_data"] = str(op["linked"])
                    op["linked"] = str(op["linked"])

        return doc

    @staticmethod
    def force_linked_keys(data: dict) -> dict:
        if "linked" not in data:
            data["linked"] = None
        if isinstance(data.get("boops"), list):
            for boop in data["boops"]:
                if "linked" not in boop:
                    boop["linked"] = None
                if isinstance(boop.get("ops"), list):
                    for op in boop["ops"]:
                        if "linked" not in op:
                            op["linked"] = None
        return data

    @staticmethod
    def skip_none_linked(doc: dict) -> bool:
        if not doc.get("linked"):
            print(f"SKIPPING DOCUMENT: {doc.get('_id')} - No linked field at document level")
            return True

        for boop in doc.get("boops", []):
            if not boop.get("linked"):
                print(f"SKIPPING DOCUMENT: {doc.get('_id')} - Boop '{boop.get('name')}' has no linked field")
                return True

            for op in boop.get("ops", []):
                if not op.get("linked"):
                    print(f"SKIPPING DOCUMENT: {doc.get('_id')} - Op '{op.get('name')}' in boop '{boop.get('name')}' has no linked field")
                    return True

        return False

    @staticmethod
    def convert_objectid_to_str(data):
        if isinstance(data, dict):
            new_data = {}
            for k, v in data.items():
                new_data[k] = GenerateBoobsService.convert_objectid_to_str(v)
            return new_data
        elif isinstance(data, list):
            return [GenerateBoobsService.convert_objectid_to_str(i) for i in data]
        elif isinstance(data, ObjectId):
            return str(data)
        else:
            return data

    @staticmethod
    def convert_dbref_to_serializable(data):
        """Convert DBRef objects to ObjectId or dictionary."""
        if isinstance(data, DBRef):
            return str(data.id)  # or data.id depending on how you want to handle it
        elif isinstance(data, dict):
            return {k: GenerateBoobsService.convert_dbref_to_serializable(v) for k, v in data.items()}
        elif isinstance(data, list):
            return [GenerateBoobsService.convert_dbref_to_serializable(i) for i in data]
        else:
            return data

    @staticmethod
    def merge_ops_with_intersection(existing_ops: List[Dict], new_ops: List[Dict]) -> List[Dict]:
        ops_map = {}

        for op in existing_ops + new_ops:
            if isinstance(op, dict) and op.get("linked"):
                linked = str(op.get("linked"))  # use the linked as a base depend key

                if linked in ops_map:
                    existing_op = ops_map[linked]

                    #merge orher fields if not exists
                    for key, value in op.items():
                        if key not in existing_op and key != "ops":
                            existing_op[key] = value
                        elif isinstance(value, list) and key == "ops":
                            existing_op[key].extend(value)
                else:
                    ops_map[linked] = op.copy()

        return list(ops_map.values())

    @staticmethod
    def merge_boops_in_group(boops_list: List[dict]) -> List[dict]:
        merged_boops = []

        for boop in boops_list:
            boop = GenerateBoobsService.convert_objectid_to_str(boop.copy())

            found_match = None
            for existing_boop in merged_boops:
                if str(boop["linked"]) == str(existing_boop["linked"]):
                    found_match = existing_boop
                    break

            if found_match is None:
                merged_boops.append(boop)
            else:
                found_match["ops"] = GenerateBoobsService.merge_ops_with_intersection(
                    existing_ops=found_match.get("ops", []),
                    new_ops=boop.get("ops", [])
                )

        return merged_boops

    @staticmethod
    def handle_boops_without_linked(boops_list: List[dict]) -> List[dict]:
        """
        This method handles boops that do not have a 'linked' field by inserting them as standalone entries.
        """
        if not GenerateBoobsService.MERGE_WITHOUT_LINKED:
            return []

        new_boops = []
        for boop in boops_list:
            if 'linked' not in boop:
                boop['linked'] = None  # Assign None to mark standalone boops
                new_boops.append(boop)
        return new_boops

    @staticmethod
    def merge_boops_ops(boops_list: List[dict]) -> List[dict]:
        merged_boops = []

        for boop in boops_list:
            boop = GenerateBoobsService.convert_objectid_to_str(boop.copy())

            found_match = None
            for existing_boop in merged_boops:
                if str(boop["linked"]) == str(existing_boop["linked"]):
                    found_match = existing_boop
                    break

            if found_match is None:
                merged_boops.append(boop)
            else:
                # Merge ops for the matching boop
                found_match["ops"] = GenerateBoobsService.merge_ops_with_intersection(
                    existing_ops=found_match.get("ops", []),
                    new_ops=boop.get("ops", [])
                )
                for key, value in boop.items():
                    if key not in found_match:
                        found_match[key] = value

        return merged_boops

    @staticmethod
    async def apply_merging_on_linked(documents: List[dict], group_key) -> List[dict]:
        grouped_docs = defaultdict(list)
        for doc in documents:
            if not group_key:
                continue
            grouped_docs[str(group_key)].append(doc)

        updated_docs = []
        for group_key, docs in grouped_docs.items():
            all_boops = []
            boops_docs = []
            properties_combination = []
            # Map to store boops per tenant
            tenant_boops_map = {}

            for doc in docs:
                tenant_name = doc.get('tenant_name', '')
                tenant_id = doc.get('tenant_id', '')

                properties_list = []
                tenant_boops_list = []
                
                for bo in doc.get("boops", []):
                    # Skip boop if it has no linked
                    if "linked" not in bo:
                        continue

                    ops = bo.get("ops", [])

                    # Skip boop if any op has no linked
                    if any("linked" not in op for op in ops):
                        continue

                    # Process ops (excludes etc.)
                    excludes = []
                    option_id = None
                    ex_data = {}
                    for op in ops:
                        option_id = str(op.get("_id"))
                        excludes.extend(op.get("excludes", []))
                        ex_data[str(option_id)] = excludes

                    #for op in ops:
                    #    op['excludes'] = []

                    # Add only if all linked checks passed
                    properties_list.append({"linked" : str(bo['linked']), "slug": bo['slug']})
                    tenant_boops_list.append(bo)

                # Store tenant boops for later merging
                if tenant_id not in tenant_boops_map:
                    tenant_boops_map[tenant_id] = []
                tenant_boops_map[tenant_id].extend(tenant_boops_list)

                # Append clean set to properties_combination
                properties_combination.append({
                    "tenant_name": tenant_name,
                    "tenant_id": tenant_id,
                    "properties": properties_list
                })

                all_boops.extend(doc.get("boops", []))

            standalone_boops = GenerateBoobsService.handle_boops_without_linked(all_boops)
            all_boops = [boop for boop in all_boops if 'linked' in boop]
            merged_boops = GenerateBoobsService.merge_boops_ops(all_boops)
            merged_boops.extend(standalone_boops)

            # Merge boops for each tenant
            tenant_merged_boops = {}
            for tenant_id, tenant_boops in tenant_boops_map.items():
                tenant_filtered = [b for b in tenant_boops if 'linked' in b]
                tenant_merged = GenerateBoobsService.merge_boops_ops(tenant_filtered)
                tenant_merged_boops[tenant_id] = tenant_merged

            # Enrich properties_combination with tenant-specific boops
            for prop_combo in properties_combination:
                tenant_id = prop_combo.get('tenant_id')
                if tenant_id and tenant_id in tenant_merged_boops:
                    prop_combo['boops'] = tenant_merged_boops[tenant_id]

            # Handle group_key (convert to ObjectId if necessary)
            if isinstance(group_key, str):
                try:
                    group_key = ObjectId(group_key)
                except InvalidId:
                    pass

            # Fetch the main document by group_key
            main_base_doc = await categories_collection.find_one({"_id": group_key})
            if main_base_doc:
                base_doc = main_base_doc.copy()
                base_doc["boops"] = merged_boops
                base_doc["properties_manifest"] = properties_combination
                # Convert any DBRef to serializable format
                cleaned = GenerateBoobsService.convert_dbref_to_serializable(base_doc)
                cleaned = GenerateBoobsService.preserve_linked_as_data(cleaned)
                updated_docs.append(cleaned)

        return updated_docs

class BoobsRequest(BaseModel):
    sku: Optional[str] = None
    linked: Optional[str] = None
    tenant_id: Optional[str] = None


async def map_supplier_option_ids_to_origin(supplier_option_ids, db):
    """
    Maps supplier option IDs to their original option IDs using the 'linked' field.
    Returns a dictionary: {supplier_option_id: original_option_id}
    """
    supplier_options_collection = db["supplier_options"]
    mapping = {}
    
    # Convert string IDs to ObjectId for querying
    object_ids = []
    for sid in supplier_option_ids:
        try:
            object_ids.append(ObjectId(sid))
        except:
            pass
    
    if not object_ids:
        return mapping
    
    # Query all supplier options at once
    cursor = supplier_options_collection.find({"_id": {"$in": object_ids}})
    supplier_options = await cursor.to_list(None)
    
    # Build the mapping
    for supplier_opt in supplier_options:
        supplier_id = str(supplier_opt.get("_id"))
        linked_id = supplier_opt.get("linked")
        if linked_id:
            mapping[supplier_id] = str(linked_id)
    
    return mapping


def build_supplier_to_origin_map_from_boops(results):
    """
    Builds a mapping from supplier option IDs to original option IDs
    using the existing boops data (id -> linked).
    Returns a dictionary: {supplier_option_id: original_option_id}
    """
    mapping = {}
    
    for item in results:
        # Process main boops array
        for boop in item.get("boops", []):
            for op in boop.get("ops", []):
                supplier_id = str(op.get("id", ""))
                linked_id = str(op.get("linked", ""))
                if supplier_id and linked_id:
                    mapping[supplier_id] = linked_id
        
        # Also process boops inside properties_manifest
        for prop_manifest in item.get("properties_manifest", []):
            for boop in prop_manifest.get("boops", []):
                for op in boop.get("ops", []):
                    supplier_id = str(op.get("id", ""))
                    linked_id = str(op.get("linked", ""))
                    if supplier_id and linked_id:
                        mapping[supplier_id] = linked_id
    
    return mapping


def process_boop_excludes(boop, supplier_to_origin_map):
    """Helper function to process excludes in a boop's ops"""
    for op in boop.get("ops", []):
        # Replace supplier option IDs with original option IDs in excludes
        if "excludes" in op:
            new_excludes = []
            for exclude_group in op.get("excludes", []):
                if isinstance(exclude_group, list):
                    # Map each ID in the group
                    new_group = [
                        supplier_to_origin_map.get(sid, sid) 
                        for sid in exclude_group
                    ]
                    new_excludes.append(new_group)
                else:
                    # Single ID
                    new_excludes.append(supplier_to_origin_map.get(exclude_group, exclude_group))
            op["excludes"] = new_excludes


async def enrich_origin_values(results, db):
    boxes_collection = db["boxes"]
    options_collection = db["options"]

    # Build mapping from supplier option IDs to original option IDs using existing boops data
    supplier_to_origin_map = build_supplier_to_origin_map_from_boops(results)

    for item in results:
        # Process main boops array
        for boop in item.get("boops", []):
            linked_box_id = boop.get("linked")
            if linked_box_id:
                try:
                    box_doc = await boxes_collection.find_one({"_id": ObjectId(linked_box_id)})
                    if box_doc:
                        for key in boop.keys():
                            if key in box_doc:
                                value = box_doc[key]
                                # change the value from object to str
                                boop[key] = str(value) if isinstance(value, ObjectId) else value
                except:
                    pass

            for op in boop.get("ops", []):
                linked_option_id = op.get("linked")
                if linked_option_id:
                    try:
                        option_doc = await options_collection.find_one({"_id": ObjectId(linked_option_id)})
                        if option_doc:
                            for key in op.keys():
                                if key in option_doc:
                                    value = option_doc[key]
                                    op[key] = str(value) if isinstance(value, ObjectId) else value
                    except:
                        pass
            
            # Process excludes for main boops
            process_boop_excludes(boop, supplier_to_origin_map)
        
        # Process boops inside properties_manifest
        for prop_manifest in item.get("properties_manifest", []):
            for boop in prop_manifest.get("boops", []):
                # Process excludes for properties_manifest boops
                process_boop_excludes(boop, supplier_to_origin_map)

    return results

@app.get("/manifest-merged-boops")
async def get_merged_boops(request: BoobsRequest):
   main_response = []
   try:
       tn_id = ""
       sku = str(request.sku)
       category = await categories_collection.find_one({'slug': sku})
       if category:
           tn_id = str(GenerateBoobsService.convert_objectid_to_str(category).get('_id'))

       else:
           print(f"no category found {sku}")
           return {"message": f"no category found {sku}"}


       try:
           linked_obj_id = ObjectId(tn_id)
       except InvalidId:
           linked_obj_id = tn_id

       query = {"shareable": True, "linked": linked_obj_id}
       query2 = {}

       if request.tenant_id:
           query = {"shareable": True, "linked": linked_obj_id, "tenant_id": {"$ne": str(request.tenant_id)}}

           query2 = {"tenant_id": str(request.tenant_id), "published": True, "linked": linked_obj_id}
           shareable_boobs_tenant = await boobs_collection.find(query2).to_list(None)
           main_response.extend([
               doc for doc in shareable_boobs_tenant
               if not GenerateBoobsService.skip_none_linked(doc)
           ])

       shareable_boobs = await boobs_collection.find(query).to_list(None)
       # main_response.extend(shareable_boobs)
       main_response.extend([
           doc for doc in shareable_boobs
           if not GenerateBoobsService.skip_none_linked(doc)
       ])

       if not main_response:
           print("no sharable boobs found")
           return []

       # Process the documents and merge
       result = await GenerateBoobsService.apply_merging_on_linked(main_response, linked_obj_id)
       results = GenerateBoobsService.convert_objectid_to_str(result)

       final_results = await enrich_origin_values(results, db)

       return final_results
   except Exception as e:
       print(f"error {str(e)}")
       return []


@app.post("/manifest-merged-boops-by-linked")
async def get_merged_boops_by_linked(request: BoobsRequest):
   main_response = []
   try:
       linked_id = str(request.linked)
       
       try:
           linked_obj_id = ObjectId(linked_id)
       except InvalidId:
           linked_obj_id = linked_id

       query = {"shareable": True, "linked": linked_obj_id}
       query2 = {}

       if request.tenant_id:
           query = {"shareable": True, "linked": linked_obj_id, "tenant_id": {"$ne": str(request.tenant_id)}}

           query2 = {"tenant_id": str(request.tenant_id), "published": True, "linked": linked_obj_id}
           shareable_boobs_tenant = await boobs_collection.find(query2).to_list(None)
           main_response.extend([
               doc for doc in shareable_boobs_tenant
               if not GenerateBoobsService.skip_none_linked(doc)
           ])

       shareable_boobs = await boobs_collection.find(query).to_list(None)
       main_response.extend([
           doc for doc in shareable_boobs
           if not GenerateBoobsService.skip_none_linked(doc)
       ])

       if not main_response:
           print("no sharable boops found")
           return []

       # Process the documents and merge
       result = await GenerateBoobsService.apply_merging_on_linked(main_response, linked_obj_id)
       results = GenerateBoobsService.convert_objectid_to_str(result)

       final_results = await enrich_origin_values(results, db)

       return final_results
   except Exception as e:
       print(f"error {str(e)}")
       return []
