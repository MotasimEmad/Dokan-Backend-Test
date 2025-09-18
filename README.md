# Laravel Blog API

A comprehensive Laravel blog API built with PostgreSQL, Laravel Sanctum authentication, and comprehensive testing.

## Tech Stack

- **Laravel** (latest stable)
- **PostgreSQL** as the database
- **Laravel Sanctum** for authentication
- **Eloquent** for models & relationships
- **Laravel Resource** classes for API responses
- **Laravel Policies** for authorization
- **Form Request** classes for validation
- **Feature Tests** with PHPUnit

## Models & Relationships

### User
- Default Laravel user model with Sanctum tokens
- Has many Posts and Comments

### Post
- `title` (string)
- `content` (text)
- `user_id` (foreign key)
- `category_id` (foreign key)
- Soft deletes enabled
- Belongs to User & Category, has many Comments

### Comment
- `content` (text)
- `user_id` (foreign key)
- `post_id` (foreign key)
- Soft deletes enabled
- Belongs to User & Post

### Category
- `name` (string)
- Has many Posts

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user


### Posts
- `GET /api/posts` - List all posts with pagination (10 per page), user & category names, comment count, and 5 recent comments per post
- `POST /api/posts` - Create a post (authenticated)
- `GET /api/posts/{id}` - Show a single post with separate post object and paginated comments (8 per page)
- `PUT /api/posts/{id}` - Update a post (authenticated, owner only)
- `DELETE /api/posts/{id}` - Delete a post (authenticated, owner only)

### Comments
- `POST /api/posts/{id}/comments` - Add a comment to a post (authenticated)
- `GET /api/comments/{id}` - Show a single comment
- `PUT /api/comments/{id}` - Update a comment (authenticated, owner only)
- `DELETE /api/comments/{id}` - Delete a comment (authenticated, owner only)

### Categories
- `GET /api/categories/{id}/posts` - List posts by category with pagination (15 per page) and 5 recent comments per post

## Performance Optimizations

### 🚀 Smart Comment Loading Strategy

To prevent performance issues when dealing with posts that have many comments, we've implemented a smart loading strategy:

#### **Posts List Endpoint (`GET /api/posts`)**
- **Shows only 5 recent comments** per post
- **Still displays total comment count** for UI reference
- **Paginated posts** (10 per page by default)
- **Performance Benefit**: Instead of loading 1,000 comments × 15 posts = 15,000 comments, we only load 5 × 15 = 75 comments maximum

#### **Single Post Endpoint (`GET /api/posts/{id}`)**
- **Separate post object** and **paginated comments array**
- **Default**: 8 comments per page
- **Maximum**: 100 comments per page
- **Query parameter**: `comments_per_page` to customize
- **Performance Benefit**: Only loads the requested page of comments

### 📊 Performance Impact Example

**Before Optimization:**
```bash
# Loading 15 posts with 1,000 comments each
GET /api/posts
# Result: 15,000 comments loaded = Slow response, high memory usage
```

**After Optimization:**
```bash
# Loading 15 posts with 5 recent comments each
GET /api/posts
# Result: 75 comments loaded = Fast response, low memory usage

# Loading full comments for specific post
GET /api/posts/1?comments_per_page=20
# Result: Only 20 comments loaded = Optimal performance
```

### 🔧 Query Parameters

#### **Posts Pagination:**
- `per_page` - Number of posts per page (1-100, default: 10)
- `page` - Page number (default: 1)

#### **Comments Pagination (Single Post):**
- `comments_per_page` - Number of comments per page (1-100, default: 8)
- `page` - Page number (default: 1)

### 📈 Response Examples

#### **Posts List Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Post Title",
            "content": "Post content...",
            "user": "John Doe",
            "category": "Technology",
            "comments_count": 1000,
            "comments": [
                // Only 5 most recent comments
                { "id": 1000, "content": "Latest comment...", "user": {...} },
                { "id": 999, "content": "Second latest...", "user": {...} },
                // ... 3 more recent comments
            ],
            "created_at": "2025-09-18T20:00:00.000000Z",
            "updated_at": "2025-09-18T20:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "total": 50,
        "from": 1,
        "to": 10,
        "has_more_pages": true
    }
}
```

#### **Single Post Response:**
```json
{
    "success": true,
    "post": {
        "id": 1,
        "title": "Post Title",
        "content": "Post content...",
        "user": "John Doe",
        "category": "Technology",
        "created_at": "2025-09-18T20:00:00.000000Z",
        "updated_at": "2025-09-18T20:00:00.000000Z"
    },
    "comments": {
        "data": [
            // 8 comments (paginated)
            { "id": 1000, "content": "Latest comment...", "user": {...} },
            { "id": 999, "content": "Second latest...", "user": {...} },
            // ... 6 more comments
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 125,
            "per_page": 8,
            "total": 1000,
            "from": 1,
            "to": 8,
            "has_more_pages": true
        }
    }
}
```

## Authorization Rules

- Only post owners can update or delete their posts
- Only comment owners can update or delete their comments
- Only authenticated users can create posts or comments
- All authorization is enforced using Laravel Policies

## Validation Rules

### Post Creation
- `title`: required, string, max 255 characters
- `content`: required, string
- `category_id`: required, must exist in categories table

### Comment Creation
- `content`: required, string

### User Registration
- `name`: required, string, max 255 characters
- `email`: required, email, unique
- `password`: required, string, min 8 characters

## Setup Instructions

### Prerequisites
- PHP 8.1 or higher
- Composer
- PostgreSQL
- Node.js (for frontend assets)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-blog-api
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   Update your `.env` file with PostgreSQL credentials:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel_blog
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed Database (Optional)**
   ```bash
   php artisan db:seed
   ```

8. **Serve the Application**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

## Testing

Run the comprehensive test suite:

```bash
# Run all tests
php artisan test

# Run only feature tests
php artisan test --testsuite=Feature

# Run specific test class
php artisan test tests/Feature/PostTest.php
```

### Test Coverage

The test suite includes:
- ✅ Creating a post as an authenticated user
- ✅ Failing to create a post when unauthenticated
- ✅ Posting a comment to a post
- ✅ Viewing a post with all its comments
- ✅ Authorization tests (owners can update/delete, others cannot)
- ✅ Validation tests
- ✅ Authentication flow (register, login, logout)
- ✅ Soft delete functionality

## API Usage Examples

### Register a User
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Create a Post (with authentication token)
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "My First Post",
    "content": "This is the content of my first post.",
    "category_id": 1
  }'
```

### Get All Posts (with pagination)
```bash
# Default pagination (10 posts per page)
curl -X GET http://localhost:8000/api/posts

# Custom pagination
curl -X GET "http://localhost:8000/api/posts?per_page=20&page=2"
```

### Get Single Post (with comment pagination)
```bash
# Default comment pagination (8 comments per page)
curl -X GET http://localhost:8000/api/posts/1

# Custom comment pagination
curl -X GET "http://localhost:8000/api/posts/1?comments_per_page=20&page=1"
```

### Get Posts by Category (with pagination)
```bash
# Default pagination (15 posts per page)
curl -X GET http://localhost:8000/api/categories/1/posts

# Custom pagination
curl -X GET "http://localhost:8000/api/categories/1/posts?per_page=5&page=1"
```

### Add a Comment
```bash
curl -X POST http://localhost:8000/api/posts/1/comments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "content": "Great post!"
  }'
```

## Features

### ✅ Implemented Features
- Complete CRUD operations for Posts, Comments, and Categories
- Laravel Sanctum authentication
- Authorization policies for secure access control
- Form request validation
- API resource classes for structured responses
- Soft deletes for Posts and Comments
- **Performance-optimized pagination** for posts and comments
- **Smart comment loading** (5 recent comments in list, paginated in details)
- **Global API response format** with success/error handling
- Comprehensive feature tests
- Model factories for testing
- Database seeders

### 🔧 Technical Features
- RESTful API design
- Proper HTTP status codes
- JSON responses
- Relationship loading optimization
- Mass assignment protection
- CSRF protection (where applicable)
- Input validation and sanitization

## Database Schema

### Users Table
- `id` (primary key)
- `name`
- `email` (unique)
- `email_verified_at`
- `password`
- `remember_token`
- `created_at`
- `updated_at`

### Categories Table
- `id` (primary key)
- `name`
- `created_at`
- `updated_at`

### Posts Table
- `id` (primary key)
- `title`
- `content`
- `user_id` (foreign key to users)
- `category_id` (foreign key to categories)
- `deleted_at` (soft delete)
- `created_at`
- `updated_at`

### Comments Table
- `id` (primary key)
- `content`
- `user_id` (foreign key to users)
- `post_id` (foreign key to posts)
- `deleted_at` (soft delete)
- `created_at`
- `updated_at`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).