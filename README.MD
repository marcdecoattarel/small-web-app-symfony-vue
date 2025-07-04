# Symfony + Vue.js Coding Test: Track Management Mini App

## ✨ Objective
Create a small web application to manage music tracks. Build a REST API with Symfony and a Vue.js frontend to interact with it. Keep it lightweight—the whole test should take ~90 minutes.

---

## 🔗 Part 1: Symfony Backend API

### 📂 Requirements
Build a REST API with the following capabilities:

**Track entity:**
- `id` (auto-incremented integer)
- `title` (string, required)
- `artist` (string, required)
- `duration` (integer, in seconds, required)
- `isrc` (string, optional, must match `^[A-Z]{2}-[A-Z0-9]{3}-\d{2}-\d{5}$`)

### 🔍 API Endpoints
- `GET /api/tracks` - List all tracks
- `POST /api/tracks` - Create a new track
- `PUT /api/tracks/{id}` - Update an existing track

### ✅ Expectations
- Symfony 6+
- Doctrine ORM
- Form validation (e.g., NotBlank, Regex)
- JSON responses with validation errors
- No authentication required

### 📆 Bonus
- Use a service class for business logic
- Return clean error messages

### 🔧 Setup Instructions (Backend)
1. Clone the repository.
2. Run `composer install`
3. Copy `.env` to `.env.local` and configure your DB credentials
4. Run migrations:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
5. Run the Symfony server:
   ```bash
   symfony serve
   ```

---

## 🎮 Part 2: Vue.js Frontend

### 🔧 Requirements
Build a small interface to:
- Display tracks in a table
- Create a new track via a form
- Edit an existing track via the same form

### ⚡ Tech Stack
- Vue 3 + Composition API
- Pinia (or reactive refs)
- Axios (or Fetch API)
- Basic validation feedback (required fields, ISRC format)

### ✅ Expectations
- List auto-updates after creating or editing
- Clear form on submit success
- Minimal styling needed

### 💡 Bonus
- Duration input as `mm:ss`, converted to seconds
- Modal or expandable form for editing

### 🔧 Setup Instructions (Frontend)
1. Navigate to the `frontend/` directory
2. Install dependencies:
   ```bash
   npm install
   ```
3. Run the dev server:
   ```bash
   npm run dev
   ```
4. Ensure the API is accessible at `http://localhost:8000/api/tracks`

---

## ⏳ Time Budget
- Backend: ~45-60 mins
- Frontend: ~30-45 mins

---

## ⚖️ Evaluation Rubric
| Area                      | Weight |
|---------------------------|--------|
| Symfony REST/API design   | 3      |
| Doctrine + Validation     | 2      |
| Vue structure & reactivity| 2      |
| UX (form & updates)       | 2      |
| Bonus: polish & extras    | 1      |

---

## 📖 Submission Instructions
- Submit code as a GitHub repo or ZIP file
- Ensure both backend and frontend run independently
- Include setup instructions in each folder (`README.md`)
- Leave comments where appropriate to explain design choices

Let us know if anything was unclear or if you'd like to explain your thought process!
