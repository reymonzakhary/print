from fastapi import FastAPI
from services.redisearch_service import RedisearchService
from services.scheduler_service import SchedulerService
from services.helper import Helper
from routes.search_routes import router as search_router
import threading

def create_app():
    """
    Factory function to create and configure the FastAPI application.
    """
    app = FastAPI(title="Search Engine API", version="2.0")

    # Register routes
    app.include_router(search_router)

    @app.on_event("startup")
    async def startup_event():
        """Run startup tasks after app is fully started"""
        print("FastAPI server is starting up, Server is ready for requests ...")

    return app


def main():
    """
    Main function to initialize services and run the FastAPI application.
    """
    # Initialize and start scheduler
    helper = Helper()
    helper.clear_indixing()
    helper.create_indexing()

    # Start background initialization in a separate thread
    init_thread = threading.Thread(target=helper.generate_data_set, daemon=True)
    init_thread.start()
    print("Background initialization started - server will be ready for requests immediately!")

    scheduler_service = SchedulerService()
    scheduler_service.start_scheduler()
    print("Daily scheduler started - will run re-sync at 11:00 PM daily")

    # Start FastAPI server
    app = create_app()
    return app


# FastAPI app instance
app = main()