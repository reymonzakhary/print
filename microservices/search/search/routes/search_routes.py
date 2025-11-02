from fastapi import APIRouter, Depends
from services.generate_data_set_service import DataSetService
from services.redisearch_service import RedisearchService
from services.boxes_redisearch_service import RedisearchService as BoxSearchService
from services.box_data_set_service import DataSetService as BoxDataSetService
from services.options_data_set_service import DataSetService as OptionDataSetService
from services.options_redisearch_service import RedisearchService as OptionSearchService
from services.scheduler_service import SchedulerService
from services.helper import Helper
import threading

router = APIRouter()

# Inject Redisearch Services
redis_service = RedisearchService()
box_service = BoxSearchService()
box_data_service = BoxDataSetService()

option_service = OptionSearchService()
option_data_service = OptionDataSetService()

# Inject DataSet Service
dataset_service = DataSetService()

# Inject Scheduler Service
scheduler_service = SchedulerService()

# Global flag to track initialization status
initialization_complete = False

@router.get("/healthz")
def health_check():
    """Health check endpoint for Kubernetes readiness probe."""
    return {"status": "healthy", "message": "Search service is running"}

@router.get("/healthz-startup")
def startup_check():
    """Startup check endpoint for Kubernetes startup probe."""
    global initialization_complete
    if initialization_complete:
        return {"status": "ready", "message": "Search service initialization complete"}
    else:
        return {"status": "initializing", "message": "Search service is still initializing"}, 503

@router.get("/info")
def get_redis_info():
    """Check Redis connection and memory usage."""
    try:
        return redis_service.get_redis_info()
    except Exception as e:
        return {
            "status": "error",
            "error": str(e)
        }

@router.get("/category-search")
def category_autocomplete(query: str = None, iso: str = None, limit: int = 30):
    """Handle category autocomplete requests."""
    result = redis_service.search_categories(query or " ", iso, limit)
    return {"suggestions": result}

@router.get("/categories-search")
def category_autocomplete(query: str = None, iso: str = None, limit: int = 30):
    """Handle category autocomplete requests."""
    result = redis_service.search_categories(query or " ", iso, limit)
    return {"suggestions": result}

@router.get("/boxes-search")
def boxes_autocomplete(query: str = None, iso: str = None, limit: int = 30):
    """Handle boxes autocomplete requests."""
    result = box_service.search_boxes(query or " ", iso, limit)
    return {"suggestions": result}

@router.get("/options-search")
def options_autocomplete(query: str = None, iso: str = None, limit: int = 30):
    """Handle options autocomplete requests."""
    result = option_service.search_options(query or " ", iso, limit)
    return {"suggestions": result}                                                                                                                                                                                                                                                                                                                                                                                                                                                

@router.get("/generate-data-set")
def generate_data_set():
    """Fetch categories from MongoDB and store them in `data/data_set.json`."""
    # option_data_service.generate_data_set()
    box_data_service.generate_data_set()
    return dataset_service.generate_data_set()

@router.get("/clear-data")
def clear_data():
    """Clear categories data from MongoDB and Redis."""
    return dataset_service.clear_data_set()

@router.get("/generate-box-data")
def generate_box_data():
    """Fetch boxes from MongoDB and insert them into Redis."""
    return box_data_service.generate_data_set()

@router.get("/generate-option-data")
def generate_option_data():
    """Fetch options from MongoDB and insert them into Redis."""
    return option_data_service.generate_data_set()

@router.post("/sync")
def initialize_data():
    """Complete re-sync of all data types (categories, boxes, options)."""
    try:
        helper = Helper()
        helper.clear_indixing()
        helper.create_indexing()

        # Start background initialization in a separate thread
        init_thread = threading.Thread(target=helper.generate_data_set, daemon=True)
        init_thread.start()
        print("Background initialization started - server will be ready for requests immediately!")

        return {
            "message": "Success Resynced",
            "status": "200"
        }
    except Exception as e:
        return {
            "message": "Re-sync failed",
            "error": str(e),
            "status": "422"
        }

@router.get("/sync-status")
def get_sync_status():
    """Get the current sync status and last sync information."""
    try:
        return scheduler_service.get_sync_status()
    except Exception as e:
        return {
            "status": "error",
            "error": str(e)
        }

@router.post("/scheduler/start")
def start_scheduler():
    """Start the automatic re-sync scheduler."""
    try:
        scheduler_service.start_scheduler()
        return {
            "message": "Scheduler started successfully",
            "status": "running"
        }
    except Exception as e:
        return {
            "message": "Failed to start scheduler",
            "error": str(e),
            "status": "error"
        }

@router.post("/scheduler/stop")
def stop_scheduler():
    """Stop the automatic re-sync scheduler."""
    try:
        scheduler_service.stop_scheduler()
        return {
            "message": "Scheduler stopped successfully",
            "status": "stopped"
        }
    except Exception as e:
        return {
            "message": "Failed to stop scheduler",
            "error": str(e),
            "status": "error"
        }

@router.get("/scheduler/status")
def get_scheduler_status():
    """Get the current scheduler status and job information."""
    try:
        return scheduler_service.get_scheduler_status()
    except Exception as e:
        return {
            "status": "error",
            "error": str(e)
        }

@router.post("/scheduler/trigger-now")
def trigger_manual_sync():
    """Manually trigger a re-sync immediately."""
    try:
        scheduler_service.trigger_manual_sync()
        return {
            "message": "Manual re-sync triggered successfully",
            "status": "triggered"
        }
    except Exception as e:
        return {
            "message": "Failed to trigger manual re-sync",
            "error": str(e),
            "status": "error"
        }

@router.post("/categories")
def add_category_to_search(category_data: dict):
    """Add a new category directly to the search engine."""
    try:
        # Use the dataset service to add single category
        result = dataset_service.add_single_category(category_data)
        return {
            "message": "Category added to search engine successfully",
            "status": "success",
            "data": result
        }
    except Exception as e:
        return {
            "message": "Failed to add category to search engine",
            "error": str(e),
            "status": "error"
        }

@router.delete("/categories")
def delete_category_from_search(delete_data: dict):
    """
    Delete category entries from the search engine.
    
    Expected body format:
    {
        "linked": "6823429a9819c80e6ff7b1b4",
        "display_name": [
            {"iso": "en", "display_name": "Briefpapier"},
            {"iso": "fr", "display_name": "Briefpapier"},
            {"iso": "nl", "display_name": "Briefpapier"}
        ]
    }
    """
    try:
        linked = delete_data.get("linked")
        display_names = delete_data.get("display_names", [])
        
        if not linked:
            return {
                "message": "Missing required field: linked",
                "status": "error"
            }
        
        if not display_names or not isinstance(display_names, list):
            return {
                "message": "Missing or invalid field: display_name (must be a list)",
                "status": "error"
            }
        
        result = dataset_service.delete_single_category(linked, display_names)
        return result
    except Exception as e:
        return {
            "message": "Failed to delete category from search engine",
            "error": str(e),
            "status": "error"
        }

@router.put("/categories")
def upsert_category(payload: dict):
    """
    Delete old entries, then insert the new category payload.

    Expected payload:
    {
      "source": { "linked": "ORIGIN_ID", "slug": "old-slug", "origin_name": "old", "display_names": [ {"iso": "en", "display_name": "old"} ] },
      "target": { "linked": "ORIGIN_ID", "slug": "new-slug", "origin_name": "new", "display_names": [ {"iso": "en", "display_name": "new"} ], "created_at": "...", "updated_at": "..." }
    }
    """
    try:
        source = payload.get("source") or {}
        target = payload.get("target") or {}

        if not source.get("linked") or not isinstance(source.get("display_names"), list):
            return {"message": "source.linked and source.display_names[] are required", "status": "error"}

        if not target.get("linked") or not isinstance(target.get("display_names"), list):
            return {"message": "target.linked and target.display_names[] are required", "status": "error"}
        
        if not target.get("slug"):
            return {"message": "target.slug is required", "status": "error"}
        
        if not target.get("origin_name"):
            return {"message": "target.origin_name is required", "status": "error"}

        delete_result = dataset_service.delete_single_category(source["linked"], source["display_names"])
        add_result = dataset_service.add_single_category(target)

        return {
            "message": "Category updated (delete-then-insert)",
            "status": "success",
            "delete_result": delete_result,
            "add_result": add_result,
        }
    except Exception as e:
        return {"message": "Failed to update category", "error": str(e), "status": "error"}

@router.post("/boxes")
def add_box_to_search(box_data: dict):
    """Add a new box directly to the search engine."""
    try:
        # Use the box dataset service to add single box
        result = box_data_service.add_single_box(box_data)
        return {
            "message": "Box added to search engine successfully",
            "status": "success",
            "data": result
        }
    except Exception as e:
        return {
            "message": "Failed to add box to search engine",
            "error": str(e),
            "status": "error"
        }

@router.delete("/boxes")
def delete_box_from_search(delete_data: dict):
    """
    Delete box entries from the search engine.
    
    Expected body format:
    {
        "linked": "6823429a9819c80e6ff7b1b4",
        "display_name": [
            {"iso": "en", "display_name": "Briefpapier"},
            {"iso": "fr", "display_name": "Briefpapier"},
            {"iso": "nl", "display_name": "Briefpapier"}
        ]
    }
    """
    try:
        linked = delete_data.get("linked")
        display_names = delete_data.get("display_names", [])
        
        if not linked:
            return {
                "message": "Missing required field: linked",
                "status": "error"
            }
        
        if not display_names or not isinstance(display_names, list):
            return {
                "message": "Missing or invalid field: display_name (must be a list)",
                "status": "error"
            }
        
        result = box_data_service.delete_single_box(linked, display_names)
        return result
    except Exception as e:
        return {
            "message": "Failed to delete box from search engine",
            "error": str(e),
            "status": "error"
        }

@router.put("/boxes")
def upsert_box(payload: dict):
    """
    Delete old entries, then insert the new box payload.

    Expected payload:
    {
      "source": { "linked": "ORIGIN_ID", "slug": "old-slug", "origin_name": "old", "display_names": [ {"iso": "en", "display_name": "old"} ] },
      "target": { "linked": "ORIGIN_ID", "slug": "new-slug", "origin_name": "new", "display_names": [ {"iso": "en", "display_name": "new"} ], "created_at": "...", "updated_at": "..." }
    }
    """
    try:
        source = payload.get("source") or {}
        target = payload.get("target") or {}

        if not source.get("linked") or not isinstance(source.get("display_names"), list):
            return {"message": "source.linked and source.display_names[] are required", "status": "error"}

        if not target.get("linked") or not isinstance(target.get("display_names"), list):
            return {"message": "target.linked and target.display_names[] are required", "status": "error"}
        
        if not target.get("slug"):
            return {"message": "target.slug is required", "status": "error"}
        
        if not target.get("origin_name"):
            return {"message": "target.origin_name is required", "status": "error"}

        delete_result = box_data_service.delete_single_box(source["linked"], source["display_names"])
        add_result = box_data_service.add_single_box(target)

        return {
            "message": "Box updated (delete-then-insert)",
            "status": "success",
            "delete_result": delete_result,
            "add_result": add_result,
        }
    except Exception as e:
        return {"message": "Failed to update box", "error": str(e), "status": "error"}

@router.post("/options")
def add_option_to_search(option_data: dict):
    """Add a new option directly to the search engine."""
    try:
        # Use the option dataset service to add single option
        result = option_data_service.add_single_option(option_data)
        return {
            "message": "Option added to search engine successfully",
            "status": "success",
            "data": result
        }
    except Exception as e:
        return {
            "message": "Failed to add option to search engine",
            "error": str(e),
            "status": "error"
        }

@router.delete("/options")
def delete_option_from_search(delete_data: dict):
    """
    Delete option entries from the search engine.
    
    Expected body format:
    {
        "linked": "6823429a9819c80e6ff7b1b4",
        "display_name": [
            {"iso": "en", "display_name": "Briefpapier"},
            {"iso": "fr", "display_name": "Briefpapier"},
            {"iso": "nl", "display_name": "Briefpapier"}
        ]
    }
    """
    try:
        linked = delete_data.get("linked")
        display_names = delete_data.get("display_names", [])
        
        if not linked:
            return {
                "message": "Missing required field: linked",
                "status": "error"
            }
        
        if not display_names or not isinstance(display_names, list):
            return {
                "message": "Missing or invalid field: display_name (must be a list)",
                "status": "error"
            }
        
        result = option_data_service.delete_single_option(linked, display_names)
        return result
    except Exception as e:
        return {
            "message": "Failed to delete option from search engine",
            "error": str(e),
            "status": "error"
        }

@router.put("/options")
def upsert_option(payload: dict):
    """
    Delete old entries, then insert the new option payload.

    Expected payload:
    {
      "source": { "linked": "ORIGIN_ID", "slug": "old-slug", "origin_name": "old", "display_names": [ {"iso": "en", "display_name": "old"} ] },
      "target": { "linked": "ORIGIN_ID", "slug": "new-slug", "origin_name": "new", "display_names": [ {"iso": "en", "display_name": "new"} ], "created_at": "...", "updated_at": "..." }
    }
    """
    try:
        source = payload.get("source") or {}
        target = payload.get("target") or {}

        if not source.get("linked") or not isinstance(source.get("display_names"), list):
            return {"message": "source.linked and source.display_names[] are required", "status": "error"}

        if not target.get("linked") or not isinstance(target.get("display_names"), list):
            return {"message": "target.linked and target.display_names[] are required", "status": "error"}
        
        if not target.get("slug"):
            return {"message": "target.slug is required", "status": "error"}
        
        if not target.get("origin_name"):
            return {"message": "target.origin_name is required", "status": "error"}

        delete_result = option_data_service.delete_single_option(source["linked"], source["display_names"])
        add_result = option_data_service.add_single_option(target)

        return {
            "message": "Option updated (delete-then-insert)",
            "status": "success",
            "delete_result": delete_result,
            "add_result": add_result,
        }
    except Exception as e:
        return {"message": "Failed to update option", "error": str(e), "status": "error"}
