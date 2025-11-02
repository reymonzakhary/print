import os
import requests
import json
import redis
import threading
from datetime import datetime
from apscheduler.schedulers.background import BackgroundScheduler
from apscheduler.triggers.cron import CronTrigger
from dotenv import load_dotenv
from services.helper import Helper

load_dotenv()

REDIS_PORT = os.environ.get("APP_REDIS_PORT", 6380)
HOST = os.environ.get("APP_REDIS_HOST", "redisearch")

class SchedulerService:
    def __init__(self):
        self.scheduler = BackgroundScheduler()
        # Dynamic URL construction for self-calling
        self.base_url = self._get_self_url()
        self.is_running = False
        self.sync_thread = None
        self.sync_in_progress = False

        # Initialize Redis client
        self.redis_client = redis.Redis(
            host=HOST,
            port=REDIS_PORT,
            decode_responses=True
        )

    def _get_self_url(self):
        """Dynamically construct the URL for self-calling in any environment."""
        # Use 0.0.0.0 for reliable self-calling in production and development
        return "http://0.0.0.0:8000"

    def start_scheduler(self):
        """Start the background scheduler."""
        if not self.is_running:
            # Run once daily at 23:00 (11 PM), hardcoded
            self.scheduler.add_job(
                func=self.trigger_re_sync,
                trigger=CronTrigger(hour=23, minute=0),
                id='daily_re_sync',
                name='Daily Re-sync Job (23:00)',
                replace_existing=True
            )
            self.scheduler.start()
            self.is_running = True
            print("Scheduler started. Re-sync will run daily at 23:00 (11 PM).")

    def stop_scheduler(self):
        """Stop the background scheduler."""
        if self.is_running:
            self.scheduler.shutdown()
            self.is_running = False
            print("Scheduler stopped.")

    def trigger_re_sync(self):
        """Trigger the re-sync process in background thread."""
        try:
            # Check if sync is already in progress
            if self.sync_in_progress:
                print(f"[{datetime.now()}] Re-sync already in progress, skipping...")
                return

            helper = Helper()
            helper.clear_indixing()
            helper.create_indexing()
            helper.generate_data_set()

            print(f"[{datetime.now()}] Re-sync submitted to background thread")

        except Exception as e:
            print(f"[{datetime.now()}] Error scheduling re-sync: {str(e)}")

    def _run_sync_in_background(self):
        """Run the actual sync operation in background thread."""
        try:
            self.sync_in_progress = True
            print(f"[{datetime.now()}] Background sync thread started...")

            helper = Helper()
            helper.clear_indixing()
            helper.create_indexing()
            helper.generate_data_set()
        except Exception as e:
            print(f"[{datetime.now()}] Unexpected error during background re-sync: {str(e)}")
        finally:
            self.sync_in_progress = False
            print(f"[{datetime.now()}] Background sync thread completed")

    def get_scheduler_status(self):
        """Get the current status of the scheduler."""
        if not self.is_running:
            return {
                "status": "stopped",
                "message": "Scheduler is not running"
            }

        jobs = []
        for job in self.scheduler.get_jobs():
            jobs.append({
                "id": job.id,
                "name": job.name,
                "next_run": job.next_run_time.isoformat() if job.next_run_time else None,
                "trigger": str(job.trigger)
            })

        return {
            "status": "running",
            "jobs": jobs,
            "job_count": len(jobs)
        }

    def trigger_manual_sync(self):
        """Manually trigger a re-sync (for testing or immediate sync)."""
        print(f"[{datetime.now()}] Manual re-sync triggered...")
        # Always run in background thread, never block main thread
        if not self.sync_in_progress:
            self.sync_thread = threading.Thread(target=self._run_sync_in_background, daemon=True)
            self.sync_thread.start()
            print(f"[{datetime.now()}] Manual re-sync started in background thread")
        else:
            print(f"[{datetime.now()}] Re-sync already in progress, skipping...")

    def update_sync_interval(self, hours):
        """Update the sync interval."""
        # This method is no longer relevant as the scheduler is hardcoded to daily re-sync.
        # Keeping it for now, but it will not have an effect on the scheduler.
        print(f"Sync interval update to {hours} hours is not applicable for daily re-sync.")

    def get_sync_status(self):
        """Get the current sync status and last sync information."""
        try:
            # Get sync status from Redis
            status_data = self.redis_client.get("sync:status")
            redis_status = {}
            if status_data:
                redis_status = json.loads(status_data)

            # Add current sync status
            current_status = {
                "sync_in_progress": self.sync_in_progress,
                "sync_thread_alive": self.sync_thread.is_alive() if self.sync_thread else False
            }

            if status_data:
                redis_status.update(current_status)
                return redis_status
            else:
                return {
                    "status": "no_sync_data",
                    "message": "No sync data available",
                    **current_status
                }
        except Exception as e:
            return {
                "status": "error",
                "error": str(e),
                "sync_in_progress": self.sync_in_progress,
                "sync_thread_alive": self.sync_thread.is_alive() if self.sync_thread else False
            }

    def safe_initialize_data(self):
        """Safely run initialize_data in background thread - NEVER blocks main thread."""
        if self.sync_in_progress:
            print(f"[{datetime.now()}] Initialize already in progress, skipping...")
            return {"message": "Initialize already in progress", "status": "skipped"}

        print(f"[{datetime.now()}] Starting safe initialize_data in background thread...")

        # Always run in background thread
        init_thread = threading.Thread(target=self._run_initialize_in_background, daemon=True)
        init_thread.start()

        return {"message": "Initialize started in background thread", "status": "started"}

    def _run_initialize_in_background(self):
        """Run initialize_data in background thread."""
        try:
            self.sync_in_progress = True
            print(f"[{datetime.now()}] Background initialize thread started...")

            # Run the actual initialize_data
            result = self._initialize_data_internal()
            print(f"[{datetime.now()}] Background initialize completed: {result.get('message', 'Unknown')}")

        except Exception as e:
            print(f"[{datetime.now()}] Background initialize failed: {str(e)}")
        finally:
            self.sync_in_progress = False
            print(f"[{datetime.now()}] Background initialize thread completed")

    def _initialize_data_internal(self):
        """Complete re-sync of all data types (categories, boxes, options) by dropping all RediSearch indexes, deleting all related keys, and re-inserting fresh data."""
        from datetime import datetime
        import json
        from services.generate_data_set_service import DataSetService
        from services.box_data_set_service import DataSetService as BoxDataSetService
        from services.options_data_set_service import DataSetService as OptionDataSetService

        # Initialize services
        dataset_service = DataSetService()
        box_data_service = BoxDataSetService()
        # option_data_service = OptionDataSetService()

        def delete_keys_by_prefix(redis_client, prefix):
            cursor = 0
            while True:
                cursor, keys = redis_client.scan(cursor=cursor, match=f"{prefix}*")
                if keys:
                    redis_client.delete(*keys)
                if cursor == 0:
                    break

        for prefix in ["option:", "box:", "category:"]:
            delete_keys_by_prefix(self.redis_client, prefix)

        try:
            sync_start_time = datetime.now()

            # Step 1: Explicitly drop main RediSearch indexes and their data
            dropped_indexes = []
            for idx in ["idx:category", "idx:option", "idx:box"]:
                try:
                    self.redis_client.execute_command("FT.DROPINDEX", idx, "DD")
                    dropped_indexes.append(idx)
                except Exception as e:
                    print(f"Failed to drop index {idx}: {e}")

            # Step 2: Drop any other RediSearch indexes (dynamic)
            try:
                all_indexes = self.redis_client.execute_command("FT._LIST")
                for idx in all_indexes:
                    if idx not in dropped_indexes:
                        try:
                            self.redis_client.execute_command("FT.DROPINDEX", idx, "DD")
                            dropped_indexes.append(idx)
                        except Exception as e:
                            print(f"Failed to drop index {idx}: {e}")
            except Exception as e:
                print(f"Error listing indexes: {e}")

            # Step 3: Delete all related Redis keys (JSON objects)
            for prefix in ["option:", "box:", "category:"]:
                delete_keys_by_prefix(self.redis_client, prefix)

            # Step 4: Clear JSON/data files for all services
            dataset_service.clear_data_set()
            box_data_service.clear_data_set()
            # option_data_service.clear_data_set()

            # Step 5: Re-sync categories
            categories_result = {"error": "Not executed"}
            try:
                categories_result = dataset_service.generate_data_set()
                print("Categories sync completed successfully")
            except Exception as e:
                print(f"Categories sync failed: {e}")
                categories_result = {"error": str(e)}

            # Step 6: Re-sync boxes
            boxes_result = {"error": "Not executed"}
            try:
                boxes_result = box_data_service.generate_data_set()
                print("Boxes sync completed successfully")
            except Exception as e:
                print(f"Boxes sync failed: {e}")
                boxes_result = {"error": str(e)}

            # Step 7: Re-sync options
            options_result = {"error": "Not executed"}
            # try:
            #     options_result = option_data_service.generate_data_set()
            #     print("Options sync completed successfully")
            # except Exception as e:
            #     print(f"Options sync failed: {e}")
            #     options_result = {"error": str(e)}

            sync_end_time = datetime.now()
            sync_duration = (sync_end_time - sync_start_time).total_seconds()

            # Store sync status in Redis
            # Determine overall status based on individual results
            overall_status = "completed"
            if any("error" in result for result in [categories_result, boxes_result, options_result]):
                overall_status = "partial"
                if all("error" in result for result in [categories_result, boxes_result, options_result]):
                    overall_status = "failed"

            sync_status = {
                "last_sync": sync_end_time.isoformat(),
                "sync_duration_seconds": sync_duration,
                "status": overall_status,
                "categories_synced": len(categories_result.get("data-set", [])) if "error" not in categories_result else 0,
                "boxes_synced": len(boxes_result.get("data-set", [])) if "error" not in boxes_result else 0,
                "options_synced": len(options_result.get("data-set", [])) if "error" not in options_result else 0,
                "dropped_indexes": [idx.decode() if hasattr(idx, 'decode') else idx for idx in dropped_indexes],
                "errors": {
                    "categories": categories_result.get("error") if "error" in categories_result else None,
                    "boxes": boxes_result.get("error") if "error" in boxes_result else None,
                    "options": options_result.get("error") if "error" in options_result else None
                }
            }

            self.redis_client.set("sync:status", json.dumps(sync_status))

            return {
                "message": "Re-sync completed successfully",
                "sync_status": sync_status,
                "results": {
                    "categories": categories_result,
                    "boxes": boxes_result,
                    "options": options_result
                }
            }

        except Exception as e:
            # Store error status
            error_status = {
                "last_sync": datetime.now().isoformat(),
                "status": "failed",
                "error": str(e)
            }
            self.redis_client.set("sync:status", json.dumps(error_status))

            return {
                "message": "Re-sync failed",
                "error": str(e),
                "sync_status": error_status
            }

    def initialize_data(self):
        """Public method that always runs initialize_data safely in background thread."""
        return self.safe_initialize_data()
