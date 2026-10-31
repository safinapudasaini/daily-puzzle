# Test Plan and Verification Report
**Course:** CIS046-3 Software For Enterprise  
**Project:** Banana Quiz Game – Distributed Web Application  
**Execution Environment:** XAMPP (Apache, PHP 8.1.25, MariaDB 10.4.32), Windows 10/11  

---

## 1. Test Methodology

The testing strategy encompasses both **automated unit/integration tests** and **manual end-to-end user journey verification**.
- Automated tests are codified in `tests/automated_test.php` and executed via the PHP CLI.
- Manual verification covers UI event handling, network latency states, and session persistence.

---

## 2. Comprehensive Test Case Matrix

| Test ID | Test Case | Test Input / Action | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-01** | Successful Registration | Register new user `student_demo`, `student@demo.ac.uk`, `Password123!` | Account inserted into MySQL with Bcrypt hash; session established; redirected to Dashboard with success message. | User registered; password stored as `$2y$10$...`; session initiated; redirected to dashboard. | **PASS** |
| **TC-02** | Duplicate Registration Validation | Attempt to register with email `student@demo.ac.uk` or existing username | Registration rejected; friendly error message displayed indicating account already exists; database unchanged. | Form error displayed: "An account with this email address already exists. Please log in."; duplicate rejected. | **PASS** |
| **TC-03** | Successful Login | Enter valid credentials (`testuser` / `Password123!`) on `auth/login.php` | Password verified via `password_verify()`; session ID regenerated; user redirected to `dashboard/index.php`. | Authenticated successfully; session regenerated; redirected to player dashboard. | **PASS** |
| **TC-04** | Incorrect Login Credentials | Enter valid username with invalid password `WrongPass999!` | Authentication fails; generic error message displayed to prevent user enumeration; access denied. | Access denied; error message "Invalid credentials. Please verify your email/username and password." shown. | **PASS** |
| **TC-05** | Unauthorized Route Protection | Attempt to load `dashboard/index.php` or `game/play.php` without active session | Request intercepted by `includes/auth_check.php`; redirected to `auth/login.php` with error flash message. | Immediate redirection to `auth/login.php`; flash banner: "Please log in to access this page." | **PASS** |
| **TC-06** | Successful Puzzle Retrieval | Frontend requests `api/get_puzzle.php` | Backend cURL fetches puzzle from Banana API; solution stored in session; image URL sent to frontend; solution hidden. | HTTP 200 JSON; puzzle image URL returned; solution integer stored in `$_SESSION['game']['current_solution']`. | **PASS** |
| **TC-07** | Correct Answer Submission | Submit answer matching `$_SESSION['game']['current_solution']` | Server increments score by +10; feedback alert shows green success banner; HUD score counter updates. | HTTP 200 JSON `{ is_correct: true, solution: X }`; HUD score increments; green celebratory banner shown. | **PASS** |
| **TC-08** | Incorrect Answer Submission | Submit answer differing from `$_SESSION['game']['current_solution']` | Server awards 0 points; feedback alert shows red banner; reveals correct solution for learning. | HTTP 200 JSON `{ is_correct: false, solution: X }`; 0 points awarded; red banner reveals correct answer. | **PASS** |
| **TC-09** | Database Score Persistence | Complete all 5 questions in a quiz round | Final score, accuracy %, and timestamp inserted into MySQL `game_results` via PDO prepared statement. | Record inserted into `game_results` with foreign key `user_id`; verified in MySQL database. | **PASS** |
| **TC-10** | Game History Scoping | Navigate to `game/history.php` with logged-in user | Historical games displayed in tabular format; only records belonging to `$_SESSION['user_id']` are visible. | All completed games rendered; metrics match database records; foreign user records isolated. | **PASS** |
| **TC-11** | Secure Logout | Click "Logout" in navigation bar | `$_SESSION` cleared; session cookie expired; `session_destroy()` called; redirected to login page. | Session destroyed; subsequent attempts to access protected routes fail; logout notification shown. | **PASS** |
| **TC-12** | API Failure & Resilience | Simulate remote Banana API timeout or network disconnect | Backend catches cURL error; returns HTTP 503 JSON; frontend displays friendly error box with "Retry" button. | Backend logs error; frontend catches 503 and displays "External API Error" panel with functional Retry button. | **PASS** |

---

## 3. Automated Test Suite Execution Output

The test suite was executed against the local environment using:
```powershell
php tests/automated_test.php
```

### Verified Terminal Output:
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

## 4. Code Syntax Linting Verification

All 18 application source files were linted with `php -l`:
```
No syntax errors detected in index.php
No syntax errors detected in api/get_history.php
No syntax errors detected in api/get_puzzle.php
No syntax errors detected in api/reset_game.php
No syntax errors detected in api/submit_answer.php
No syntax errors detected in auth/login.php
No syntax errors detected in auth/logout.php
No syntax errors detected in auth/register.php
No syntax errors detected in config/database.php
No syntax errors detected in dashboard/index.php
No syntax errors detected in game/history.php
No syntax errors detected in game/play.php
No syntax errors detected in includes/auth_check.php
No syntax errors detected in includes/csrf.php
No syntax errors detected in includes/footer.php
No syntax errors detected in includes/functions.php
No syntax errors detected in includes/header.php
No syntax errors detected in tests/automated_test.php
```
Result: **100% Lint Clean (Zero Syntax Errors)**.
