# 🗺️ Tour Controller API Documentation

## 📋 Overview

TourController quản lý tất cả operations liên quan đến tours du lịch.

---

## 🔓 Public Endpoints (No Authentication)

### 1. Get All Tours (with filters)
```
GET /api/tours
```

**Query Parameters:**
```
?destination=Hanoi
?availability=1
?min_price=1000000
?max_price=5000000
?start_date=2025-01-01
?end_date=2025-12-31
?sort_by=priceAdult
?sort_order=asc
?per_page=15
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "tourID": 1,
        "title": "Ha Long Bay Adventure",
        "description": "Explore the beautiful Ha Long Bay...",
        "quantity": 20,
        "priceAdult": 2500000,
        "priceChild": 1500000,
        "destination": "Ha Long",
        "availability": 1,
        "startDate": "2025-11-10",
        "endDate": "2025-11-13",
        "images": [
          {
            "imageID": 1,
            "tourID": 1,
            "imageURL": "https://example.com/image1.jpg",
            "uploadDate": "2025-11-04"
          }
        ],
        "itineraries": [
          {
            "itineraryID": 1,
            "tourID": 1,
            "dayNumber": 1,
            "activity": "Day 1: Hanoi to Ha Long Bay..."
          }
        ]
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

---

### 2. Get Single Tour
```
GET /api/tours/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tourID": 1,
    "title": "Ha Long Bay Adventure",
    "description": "...",
    "images": [...],
    "itineraries": [...],
    "reviews": [
      {
        "reviewID": 1,
        "rating": 5,
        "comment": "Amazing tour!",
        "user": {
          "userID": "uuid",
          "userName": "john_doe"
        }
      }
    ]
  }
}
```

---

### 3. Get Featured Tours
```
GET /api/tours/featured
```

**Description:** Lấy 6 tours nổi bật nhất

**Response:**
```json
{
  "success": true,
  "data": [...]
}
```

---

### 4. Search Tours
```
GET /api/tours/search?keyword=hanoi
```

**Query Parameters:**
- `keyword` (required): Từ khóa tìm kiếm (min 2 ký tự)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...]
  }
}
```

---

### 5. Get Available Tours
```
GET /api/tours/available
```

**Description:** Tours còn chỗ (quantity > 0, availability = 1, startDate >= today)

**Response:**
```json
{
  "success": true,
  "data": [...]
}
```

---

### 6. Get Tours by Destination
```
GET /api/tours/destination/{destination}
```

**Example:**
```
GET /api/tours/destination/Hanoi
```

**Response:**
```json
{
  "success": true,
  "data": [...]
}
```

---

## 🔐 Admin Only Endpoints

### 7. Create Tour
```
POST /api/tours
```

**Headers:**
```
Authorization: Bearer {admin_jwt_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Ha Long Bay Adventure",
  "description": "Explore the beautiful Ha Long Bay with our 3-day tour...",
  "quantity": 20,
  "priceAdult": 2500000,
  "priceChild": 1500000,
  "destination": "Ha Long",
  "availability": 1,
  "startDate": "2025-11-10",
  "endDate": "2025-11-13",
  "images": [
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg"
  ],
  "itineraries": [
    {
      "dayNumber": 1,
      "activity": "Day 1: Hanoi to Ha Long Bay. Check in cruise, lunch, visit caves..."
    },
    {
      "dayNumber": 2,
      "activity": "Day 2: Kayaking, swimming, sunset party..."
    },
    {
      "dayNumber": 3,
      "activity": "Day 3: Morning Tai Chi, checkout, return to Hanoi"
    }
  ]
}
```

**Validation Rules:**
- `title`: required, string, max 255
- `description`: required, string
- `quantity`: required, integer, min 1
- `priceAdult`: required, numeric, min 0
- `priceChild`: required, numeric, min 0
- `destination`: required, string, max 255
- `availability`: optional, boolean (default: 1)
- `startDate`: required, date
- `endDate`: required, date, after_or_equal startDate
- `images`: optional, array of URLs
- `itineraries`: optional, array

**Response (201):**
```json
{
  "success": true,
  "message": "Tour created successfully",
  "data": {
    "tourID": 1,
    "title": "Ha Long Bay Adventure",
    ...
  }
}
```

---

### 8. Update Tour
```
PUT /api/tours/{id}
```

**Headers:**
```
Authorization: Bearer {admin_jwt_token}
Content-Type: application/json
```

**Request Body:** (All fields optional)
```json
{
  "title": "Updated Title",
  "priceAdult": 2800000,
  "quantity": 25,
  "images": [
    "https://example.com/new-image.jpg"
  ],
  "itineraries": [...]
}
```

**Note:** 
- Updating `images` will DELETE old images and add new ones
- Updating `itineraries` will DELETE old itineraries and add new ones

**Response:**
```json
{
  "success": true,
  "message": "Tour updated successfully",
  "data": {...}
}
```

---

### 9. Delete Tour
```
DELETE /api/tours/{id}
```

**Headers:**
```
Authorization: Bearer {admin_jwt_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Tour deleted successfully"
}
```

**Note:** Cascade delete images and itineraries

---

### 10. Update Tour Availability
```
PATCH /api/tours/{id}/availability
```

**Headers:**
```
Authorization: Bearer {admin_jwt_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "availability": 0
}
```

**Values:**
- `1`: Available (còn chỗ)
- `0`: Not available (hết chỗ/tạm dừng)

**Response:**
```json
{
  "success": true,
  "message": "Tour availability updated successfully",
  "data": {...}
}
```

---

### 11. Update Tour Quantity
```
PATCH /api/tours/{id}/quantity
```

**Headers:**
```
Authorization: Bearer {admin_jwt_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "quantity": 30
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tour quantity updated successfully",
  "data": {...}
}
```

---

## 🧪 Testing Examples

### Test 1: Get All Tours (Public)
```bash
curl -X GET "http://127.0.0.1:8000/api/tours?destination=Hanoi&min_price=1000000" \
  -H "Accept: application/json"
```

---

### Test 2: Search Tours (Public)
```bash
curl -X GET "http://127.0.0.1:8000/api/tours/search?keyword=beach" \
  -H "Accept: application/json"
```

---

### Test 3: Create Tour (Admin)
```bash
curl -X POST http://127.0.0.1:8000/api/tours \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Sapa Trekking Adventure",
    "description": "3-day trekking tour in Sapa mountains",
    "quantity": 15,
    "priceAdult": 3500000,
    "priceChild": 2000000,
    "destination": "Sapa",
    "startDate": "2025-12-01",
    "endDate": "2025-12-03",
    "images": [
      "https://example.com/sapa1.jpg",
      "https://example.com/sapa2.jpg"
    ],
    "itineraries": [
      {
        "dayNumber": 1,
        "activity": "Day 1: Hanoi to Sapa by night train"
      },
      {
        "dayNumber": 2,
        "activity": "Day 2: Trekking to Cat Cat village"
      },
      {
        "dayNumber": 3,
        "activity": "Day 3: Visit Fansipan peak, return to Hanoi"
      }
    ]
  }'
```

---

### Test 4: Update Tour Availability (Admin)
```bash
curl -X PATCH http://127.0.0.1:8000/api/tours/1/availability \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"availability": 0}'
```

---

### Test 5: Get Featured Tours (Public)
```bash
curl -X GET http://127.0.0.1:8000/api/tours/featured \
  -H "Accept: application/json"
```

---

## 📊 Database Schema

### Tour Table
```sql
CREATE TABLE `tour` (
  `tourID` bigint(20) PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `priceAdult` decimal(10,2) NOT NULL,
  `priceChild` decimal(10,2) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL
);
```

### Tour Images Table
```sql
CREATE TABLE `tour_images` (
  `imageID` bigint(20) PRIMARY KEY AUTO_INCREMENT,
  `tourID` bigint(20) NOT NULL,
  `imageURL` varchar(255) NOT NULL,
  `uploadDate` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tourID) REFERENCES tour(tourID)
);
```

### Tour Itinerary Table
```sql
CREATE TABLE `tour_itinerary` (
  `itineraryID` bigint(20) PRIMARY KEY AUTO_INCREMENT,
  `tourID` bigint(20) NOT NULL,
  `dayNumber` int(11) NOT NULL,
  `activity` text NOT NULL,
  FOREIGN KEY (tourID) REFERENCES tour(tourID)
);
```

---

## 🔒 Access Control

| Endpoint | Public | User | Admin |
|----------|--------|------|-------|
| `GET /tours` | ✅ | ✅ | ✅ |
| `GET /tours/{id}` | ✅ | ✅ | ✅ |
| `GET /tours/featured` | ✅ | ✅ | ✅ |
| `GET /tours/search` | ✅ | ✅ | ✅ |
| `GET /tours/available` | ✅ | ✅ | ✅ |
| `GET /tours/destination/{dest}` | ✅ | ✅ | ✅ |
| `POST /tours` | ❌ | ❌ | ✅ |
| `PUT /tours/{id}` | ❌ | ❌ | ✅ |
| `DELETE /tours/{id}` | ❌ | ❌ | ✅ |
| `PATCH /tours/{id}/availability` | ❌ | ❌ | ✅ |
| `PATCH /tours/{id}/quantity` | ❌ | ❌ | ✅ |

---

## ✅ Features

- ✅ Full CRUD operations
- ✅ Filter by destination, price, date, availability
- ✅ Search by keyword
- ✅ Pagination support
- ✅ Sort by any field
- ✅ Eager loading (images, itineraries, reviews)
- ✅ Transaction safety (DB::beginTransaction)
- ✅ Cascade delete related data
- ✅ Admin-only management
- ✅ Public viewing

---

## 🚀 Next Steps

1. Add image upload functionality (currently accepts URLs)
2. Add tour categories/tags
3. Add discount/promotion system
4. Add tour booking integration
5. Add tour reviews statistics
6. Add tour popularity tracking

---

**Status:** ✅ TourController Complete!
