# 🔄 Re-Sync Solution Guide

This guide explains how to use the comprehensive re-sync solution for categories, boxes, and options in your search microservice.

## 📋 Overview

The re-sync solution provides:
- **Unified re-sync endpoint** for all data types
- **Automatic scheduling** every 24 hours
- **Manual trigger** capabilities
- **Status monitoring** and tracking
- **Flexible configuration**

## 🚀 Quick Start

### 1. Install Dependencies

```bash
pip install -r requirements.txt
```

### 2. Set Environment Variables

Create a `.env` file with:

```env
# MongoDB Configuration
HOST=mongodb://localhost:27017
DB_NAME=admin

# Redis Configuration
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0

# Application Configuration
BASE_URL=http://localhost:8000
DATA_SET_FILE=data_set.json

# Scheduler Configuration
ENABLE_SCHEDULER=true
SYNC_INTERVAL_HOURS=24
```

### 3. Start the Service

```bash
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

The scheduler will automatically start and run re-sync every 24 hours at 2 AM.

## 🔧 API Endpoints

### Manual Re-Sync

#### Complete Re-Sync (Recommended)
```http
POST /re-sync-all
```
- Re-syncs all data types (categories, boxes, options) in one operation
- Returns detailed status and results

#### Individual Re-Sync
```http
GET /generate-data-set    # Categories only
GET /generate-box-data    # Boxes only  
GET /generate-option-data # Options only
```

### Status Monitoring

#### Check Sync Status
```http
GET /sync-status
```
Returns:
- Last sync timestamp
- Sync duration
- Number of items synced
- Current status

#### Check Scheduler Status
```http
GET /scheduler/status
```
Returns:
- Scheduler running status
- Next scheduled runs
- Job information

### Scheduler Management

#### Start Scheduler
```http
POST /scheduler/start
```

#### Stop Scheduler
```http
POST /scheduler/stop
```

#### Trigger Manual Sync
```http
POST /scheduler/trigger-now
```

#### Update Sync Interval
```http
PUT /scheduler/interval?hours=12
```

## ⏰ Scheduling Configuration

### Default Schedule
- **Daily at 2 AM**: Cron-based schedule
- **Every 24 hours**: Interval-based backup
- **Automatic start**: Enabled by default

### Customization

#### Environment Variables
```env
ENABLE_SCHEDULER=true          # Enable/disable scheduler
SYNC_INTERVAL_HOURS=24         # Interval in hours
```

#### Runtime Configuration
```http
PUT /scheduler/interval?hours=12  # Change interval
POST /scheduler/stop              # Stop scheduler
POST /scheduler/start             # Start scheduler
```

## 📊 Monitoring & Logs

### Console Output
The scheduler provides detailed console logs:
```
✅ Automatic re-sync scheduler started successfully!
[2024-01-15 02:00:00] Starting scheduled re-sync...
[2024-01-15 02:00:45] Re-sync completed successfully!
Duration: 45.2 seconds
Categories: 150
Boxes: 75
Options: 200
```

### Status Tracking
Sync status is stored in Redis and includes:
- Timestamp of last sync
- Duration of sync operation
- Number of items processed
- Success/failure status

## 🔄 Re-Sync Process

### Step-by-Step Flow

1. **Clear Data** - Remove existing Redis indexes and JSON files
2. **Fetch Categories** - Get all categories and supplier categories from MongoDB
3. **Fetch Boxes** - Get all boxes and supplier boxes from MongoDB
4. **Fetch Options** - Get all options and supplier options from MongoDB
5. **Process Data** - Transform and prepare for Redis storage
6. **Store in Redis** - Insert processed data with proper indexing
7. **Update Status** - Record sync completion and metrics

### Data Processing
- Extracts display names with ISO codes
- Handles multilingual content
- Preserves slugs and metadata
- Creates search-optimized Redis keys

## 🛠️ Troubleshooting

### Common Issues

#### Scheduler Not Starting
```bash
# Check environment variable
echo $ENABLE_SCHEDULER

# Check logs for errors
# Look for scheduler startup messages
```

#### Re-Sync Failing
```bash
# Check MongoDB connection
# Verify Redis is running
# Check data permissions
```

#### Manual Trigger Not Working
```bash
# Verify service is running
# Check endpoint availability
# Review error logs
```

### Debug Commands

#### Check Service Health
```http
GET /info
```

#### Check Sync Status
```http
GET /sync-status
```

#### Check Scheduler Status
```http
GET /scheduler/status
```

### Log Analysis
Look for these patterns in logs:
- `✅ Scheduler started` - Success
- `⚠️ Failed to start scheduler` - Error
- `Starting scheduled re-sync` - Sync beginning
- `Re-sync completed successfully` - Sync success
- `Error during scheduled re-sync` - Sync failure

## 📈 Performance Considerations

### Sync Duration
- Typical sync: 30-60 seconds
- Depends on data volume
- MongoDB connection speed
- Redis performance

### Resource Usage
- Memory: Temporary spike during sync
- CPU: Moderate during processing
- Network: Data transfer from MongoDB to Redis

### Optimization Tips
- Run during low-traffic hours
- Monitor sync duration trends
- Consider incremental updates for large datasets
- Use appropriate MongoDB indexes

## 🔐 Security Considerations

### Environment Variables
- Keep MongoDB credentials secure
- Use environment-specific configurations
- Avoid hardcoding sensitive data

### API Access
- Consider adding authentication for scheduler endpoints
- Restrict access to production environments
- Monitor API usage patterns

## 📝 Best Practices

### Production Deployment
1. Set `ENABLE_SCHEDULER=true` in production
2. Configure appropriate sync intervals
3. Monitor sync performance and logs
4. Set up alerts for sync failures

### Development
1. Use `ENABLE_SCHEDULER=false` for testing
2. Use manual triggers for development
3. Test with smaller datasets first
4. Verify data integrity after sync

### Monitoring
1. Track sync duration trends
2. Monitor success/failure rates
3. Alert on sync failures
4. Review sync logs regularly

## 🆘 Support

### Getting Help
1. Check this guide first
2. Review console logs
3. Test with manual triggers
4. Verify environment configuration

### Common Solutions
- Restart service for scheduler issues
- Check MongoDB/Redis connectivity
- Verify environment variables
- Review data permissions

---

**Need more help?** Check the main README.md for additional information about the search microservice. 