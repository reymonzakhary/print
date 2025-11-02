# 🚀 Categories Search Engine Service with Redisearch

---

## 📖 Features

- 🔎 **Search for categories by name and language**
- 🛠 **Redisearch-powered full-text search**

---

## 📌 How to Run the App

### **1️⃣ Clone the Repository**

```sh
git clone https://github.com/yourusername/your-repo.git
cd your-repo
```

### **2️⃣ Set Up Environment Variables**

Create a `.env` file in the root directory and add:

```
MONGO_URI=mongodb://your_mongo_uri
REDIS_HOST=redisearch
REDIS_PORT=6380
DATA_SET_FILE=data_set.json
DB_NAME=your_db_name
CATEGORY_COLL=categories
```

### **3️⃣ Run the App Using Docker**

```sh
docker-compose up --build
```

This will:

- Start **Redisearch** in a Docker container
- Run the **FastAPI app** at `http://localhost:8000`
- Automatically **generate the dataset** before loading it into Redis

---

## 🎯 API Endpoints

### **1️⃣ Autocomplete Search**

**🔎 Search for products by name and language**

```http
GET /autocomplete?iso={language_iso}&query={search_term}
```

#### Example:

```http
GET http://localhost:8000/autocomplete?iso=en&query=Flyers
```

#### Response:

```json
{
  "suggestions": [
    {
      "sku": "14770fcc-7197-44c6-a115-85c20141d2ed",
      "name": "Flyers"
    }
  ]
}
```

---

### **2️⃣ Health Check**

**🩺 Get Redisearsh Info / Statistics **

```http
GET /info
```

#### Example:

```http
GET http://localhost:8000/info
```

#### Response:

```json
{
    "status": "Connected to Redis",
    "index": "idx:products",
    "memory_usage_mb": "0.034763336181640625"
}
```

---

## 🛠 Built With

- **FastAPI** - Python web framework for APIs
- **Redisearch** - Full-text search engine for Redis
- **MongoDB** - NoSQL database for category storage
- **Docker** - Containerized environment

---

## 📝 Notes

- Make sure you have **Docker** installed before running the app.
- Redisearch is automatically set up inside Docker.
- MongoDB should be running before the dataset is generated.
