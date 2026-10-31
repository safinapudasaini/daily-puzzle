# 🍌 Banana Quiz Game – Distributed Web Application
**Course:** CIS046-3 Software For Enterprise  
**Academic Level:** Undergraduate University Assignment  
**Architecture:** Distributed Multi-Tier Client-Server Architecture  
**Official API:** [Marc Conrad's Banana API Documentation](https://marcconrad.com/uob/banana/doc.php)  

---

## 1. Project Overview

The **Banana Quiz Game** is an interactive, web-based mathematical puzzle application built for enterprise distributed environments. The application connects to Marc Conrad's external Banana API to fetch dynamic picture-based mathematical equations, prompts users to deduce missing values (digits 0–9), validates solutions server-side, and records game telemetry in a persistent relational database.

### Core Software Engineering Themes Demonstrated:
1. **Version Control:** Managed using Git and GitHub with isolated feature branches, atomic commits, and repository hygiene (`.gitignore`).
2. **Interoperability:** Seamless multi-tier communication linking a JavaScript frontend to a PHP 8+ backend via JSON/Fetch API, and the backend to an external cloud API via cURL/REST.
3. **Event-Driven Programming:** Decoupled, responsive user interface utilizing `addEventListener()` for keypad taps, keyboard strokes, and asynchronous network requests.
4. **Virtual Identity:** Secure authentication, Bcrypt password hashing (`password_hash()`), timing-safe verification (`password_verify()`), session lifecycle management, and user-scoped data access.

---

## 2. Technology Stack

- **Frontend:** HTML5, Modern CSS3 (CSS Grid & Flexbox, banana-inspired color palette), Vanilla JavaScript (ES6+, Fetch API).
- **Backend:** PHP 8.1+ (Object-Oriented, Strict Types).
- **Database:** MySQL / MariaDB (InnoDB, UTF-8 `utf8mb4_unicode_ci`).
- **Database Abstraction:** PHP PDO with prepared statements.
- **External Integration:** Marc Conrad's Banana API (`https://marcconrad.com/uob/banana/api.php?out=json`).
- **Local Development Environment:** XAMPP (Apache + MySQL).
- **Version Control:** Git & GitHub.
- **Data Exchange Format:** JSON.

---

## 3. Project Directory Structure

```
Daily puzzle/ (or banana_game/)
│
├── api/
│   ├── get_puzzle.php          # External Banana API proxy & session solution manager
│   ├── submit_answer.php       # Server-side answer validation & MySQL persistence
│   ├── get_history.php         # JSON endpoint returning user's game history
│   └── reset_game.php          # Session round reset endpoint
│
├── auth/
│   ├── register.php            # Virtual identity registration & password hashing
│   ├── login.php               # Virtual identity authentication & session regeneration
│   └── logout.php              # Session destruction & cookie purging
│
├── config/
│   └── database.php            # Singleton PDO database connection factory
│
├── assets/
│   ├── css/
│   │   └── style.css           # Modern, responsive gaming dashboard styling
│   ├── js/
│   │   ├── game.js             # Event-driven game controller (keypad, keyboard, Fetch API)
│   │   └── main.js             # Global utilities (CSRF meta extraction, notification lifecycle)
│   └── images/
│       └── logo.svg            # Crisp SVG brand vector logo
│
├── includes/
│   ├── header.php              # Common HTML head, navigation, & flash alerts
│   ├── footer.php              # Common footer & theme badges
│   ├── auth_check.php          # Authentication gatekeeper middleware
│   ├── csrf.php                # CSRF token generator & validator
│   └── functions.php           # Core helpers (base_url, sanitization, flash, JSON)
│
├── game/
│   ├── play.php                # Interactive game screen (HUD, puzzle card, keypad)
│   └── history.php             # Full historical game records & rating badges
│
├── dashboard/
│   └── index.php               # Player dashboard with aggregate metrics & recent games
│
├── docs/
│   ├── architecture.md         # Distributed system architecture & sequence diagrams
│   ├── assignment_themes.md    # Deep dive into the 4 themes + Viva defense guide
│   └── testing.md              # Test matrix, linting report, & verified test runs
│
├── tests/
│   └── automated_test.php      # Automated test suite (CLI test runner)
│
├── database.sql                # Complete MySQL schema & seed demo data
├── index.php                   # Public landing & enterprise overview page
├── README.md                   # Complete setup & project documentation
└── .gitignore                  # Git repository exclusion file
```

---

## 4. XAMPP Local Setup Instructions

Follow these step-by-step instructions to run the application on your local machine using XAMPP:

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
   *(Ensure both modules display green indicators showing they are active).*

### Step 2: Set Up the Project in XAMPP
You have two easy options:

#### Option A: Copy / Place Folder into `htdocs` (Recommended for XAMPP Apache)
Copy this project folder into `C:\xampp\htdocs\banana_game`.

#### Option B: Run via PHP Built-in Server (Direct from Current Folder)
Open PowerShell inside this folder and run:
```powershell
C:\xampp\php\php.exe -S localhost:8000
```

### Step 3: Import the Database
1. Open your browser and navigate to **phpMyAdmin**:  
   `http://localhost/phpmyadmin/`
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `database.sql` from the project directory.
4. Click **Import** (or **Go**) at the bottom.  
   *(Alternatively, run the SQL script directly in the SQL tab; it automatically creates the `banana_game` database and tables).*

### Step 4: Access the Application
Open your web browser and navigate to:
- If hosted in `htdocs`: `http://localhost/banana_game/`
- If using PHP server: `http://localhost:8000/`

---

## 5. Demonstration Accounts

For quick university viva demonstrations, a pre-seeded account is available:

| Role | Username / Identifier | Email | Password |
| :--- | :--- | :--- | :--- |
| **Demo Student** | `testuser` | `test@example.com` | `Password123!` |

You can also register any new account on `auth/register.php` with instant password hashing and session setup.

---

## 6. How to Run the Automated Test Suite

The project includes an automated test suite verifying database connectivity, password hashing, Banana API interoperability, scoring logic, and data isolation.

To run tests from the command line:
```powershell
C:\xampp\php\php.exe tests/automated_test.php
```

Expected Output:
```
====================================================================
   BANANA QUIZ GAME - AUTOMATED TEST SUITE (CIS046-3)              
====================================================================

 [PASS] Test 1: MySQL PDO Database Connection
 [PASS] Test 2A: Table 'users' exists
 [PASS] Test 2B: Table 'game_results' exists
 [PASS] Test 3A: password_hash() produces valid hash
 [PASS] Test 3B: password_verify() confirms correct password
 [PASS] Test 3C: password_verify() rejects incorrect password
 [PASS] Test 4A: Banana API HTTP 200 Response
 [PASS] Test 4B: Banana API returns valid 'question' URL
 [PASS] Test 4C: Banana API returns numeric 'solution'
 [PASS] Test 5A: Server correctly scores matching answer
 [PASS] Test 5B: Server correctly rejects non-matching answer
 [PASS] Test 6A: Insert game record into MySQL
 [PASS] Test 6B: Retrieved saved score accurately
 [PASS] Test 7: User-scoped query isolates data between identities

--------------------------------------------------------------------
TEST RESULTS SUMMARY: 14 Passed, 0 Failed.
====================================================================
```

---

## 7. Version Control & Git Workflow

This project was built following industry-standard Git workflow practices:

### Initializing the Repository & Branches:
```bash
# 1. Initialize local repository
git init

# 2. Configure identity
git config user.name "Your Name"
git config user.email "your.email@university.ac.uk"

# 3. Create initial commit on main
git add .gitignore
git commit -m "chore: initialize project repository and gitignore"

# 4. Create and checkout isolated development branch
git checkout -b feature/banana-game-core

# 5. Stage and commit core features atomically
git add config/ includes/ auth/ database.sql
git commit -m "feat(auth): implement user authentication, virtual identity, and database schema"

git add api/ game/ dashboard/ assets/
git commit -m "feat(game): implement Banana API integration, gameplay UI, and event controller"

git add docs/ tests/ README.md
git commit -m "docs: add architecture specifications, test report, and assignment documentation"

# 6. Merge feature branch into main
git checkout main
git merge --no-ff feature/banana-game-core -m "merge: integrate feature/banana-game-core into main"
```

### Pushing to GitHub (for university submission):
```bash
git remote add origin https://github.com/YOUR_USERNAME/banana-quiz-game.git
git branch -M main
git push -u origin main
```

---

## 8. Summary of the Four Assignment Themes for Your Tutor

During your viva or project presentation, reference these points:

1. **Version Control:** Explain that development followed branching practices (`feature/banana-game-core` merged into `main`), preventing unstabilized code from corrupting the master codebase.
2. **Interoperability:** Demonstrate that the client browser communicates with the PHP backend via asynchronous JSON/Fetch API, and PHP interoperates with the external Banana API via cURL. Emphasize that PHP acts as a secure proxy to prevent solution leakage.
3. **Event-Driven Programming:** Point to `assets/js/game.js` showing how `addEventListener()` handles keypad clicks, keyboard inputs (0–9, Enter, Backspace), and network triggers without page reloads.
4. **Virtual Identity:** Demonstrate the registration of distinct users, password hashing via `password_hash()` (Bcrypt), session protection (`session_regenerate_id()`), and verify that players can only see their own game results (`WHERE user_id = :uid`).

---

## 9. Academic Integrity & AI Assistance Statement

In compliance with academic disclosure guidelines, artificial intelligence (Google Gemini / Antigravity AI pair programming assistant) was used as a software engineering tool to assist in code generation, architectural structure design, stylesheet optimization, and test documentation. All code has been reviewed, executed, verified, and commented with full explanatory notes.
