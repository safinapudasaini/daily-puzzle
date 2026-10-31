# Demonstration of the Four Enterprise Software Themes
**Course:** CIS046-3 Software For Enterprise  
**Project:** Banana Quiz Game – Distributed Web Application  
**Student Guide & Viva Defense Reference**  

---

## Executive Summary

The **Banana Quiz Game** is built around four fundamental pillars of enterprise software engineering:
1. **Version Control** (Git & GitHub)
2. **Interoperability** (Heterogeneous API & Protocol Integration)
3. **Event-Driven Programming** (Client-Side Asynchronous Decoupling)
4. **Virtual Identity** (Authentication, Authorization, & Session Security)

This document provides both the theoretical justification and the practical code references necessary to explain these concepts during a university examination or tutor viva.

---

## Theme 1: Version Control

### 1. Theoretical Concept
Version control is the management of changes to documents, computer programs, large web sites, and other collections of information. In modern enterprise environments, Distributed Version Control Systems (DVCS) like **Git** allow engineering teams to collaborate concurrently, maintain clean audit logs, isolate unstable feature code in branches, and rollback regressions safely.

### 2. Implementation in this Project
- **Repository Structure:** Initialized locally with Git tracking all source files.
- **Ignore Rules (`.gitignore`):** Excludes operating system metadata (`Thumbs.db`, `.DS_Store`), IDE settings (`.vscode/`), and temporary logs (`*.log`), preventing repository pollution.
- **Branching Strategy:**
  - `main`: Stable, release-ready production branch.
  - `feature/banana-game-core`: Isolated development branch used to implement core gameplay, authentication, and external API integration prior to merging.
- **Atomic Commits:** Meaningful commit messages adhering to standard conventional commit guidelines (e.g. `feat(api): ...`, `fix(auth): ...`).

### 3. Viva Defense (How to explain to your tutor)
> **Tutor Question:** *"Why did you use branches instead of committing everything straight to main?"*  
> **Student Answer:** *"In enterprise software engineering, the `main` branch represents deployable production code. By developing features on `feature/banana-game-core`, I isolated experimental work, ensured automated tests passed, and performed a clean merge into `main` without risking unstable code reaching production."*

---

## Theme 2: Interoperability

### 1. Theoretical Concept
Interoperability is the characteristic of a product or system to cooperate and exchange information with other products or systems, without any restriction or dependency on a specific operating system, language, or vendor. It is powered by open protocols (HTTP/HTTPS) and standardized data formats (JSON, XML).

### 2. Implementation in this Project
This application demonstrates **two distinct tiers of interoperability**:

```
[ Browser / JS ] <==== HTTP / JSON ====> [ PHP Backend ] <==== HTTP / REST / cURL ====> [ Banana Cloud API ]
```

1. **Client-to-Backend Interoperability:**
   - **Protocol:** HTTP/1.1 over asynchronous JavaScript **Fetch API**.
   - **Data Exchange:** Standard JSON payloads (`application/json`).
   - **Decoupling:** The frontend JavaScript client does not care whether the backend is written in PHP, Python, or Go, as long as the JSON contract is respected.

2. **Backend-to-External Cloud Interoperability:**
   - **Protocol:** Server-side **cURL** communicating with Marc Conrad's official Banana API (`https://marcconrad.com/uob/banana/api.php?out=json`).
   - **Resilience & Fault Tolerance:** If the external Banana API times out or returns an error, `api/get_puzzle.php` catches the failure, logs the incident, and delivers an instructional JSON error message allowing the client to retry.

### 3. Code References
- `assets/js/game.js`: Native `fetch()` calls transmitting HTTP headers and parsing JSON.
- `api/get_puzzle.php`: Server-side `curl_init()` and `curl_exec()` fetching remote puzzle assets.

### 4. Viva Defense (How to explain to your tutor)
> **Tutor Question:** *"Why doesn't the JavaScript frontend fetch the Banana API directly?"*  
> **Student Answer:** *"Direct browser access to the Banana API would create two major enterprise flaws: first, it violates CORS (Cross-Origin Resource Sharing) policies; second, it would leak the secret answer (`solution`) directly into browser network tabs, allowing players to cheat. By using PHP as an interoperability proxy, the server securely holds the solution in session memory and transmits only the puzzle image to the client."*

---

## Theme 3: Event-Driven Programming

### 1. Theoretical Concept
Event-driven programming is a programming paradigm in which the flow of the program is determined by events—user actions (mouse clicks, key presses), sensor outputs, or messages from other programs or threads. The architecture decouples **event emitters** from **event handlers**.

### 2. Implementation in this Project
In `assets/js/game.js`, all user interaction and asynchronous responses follow strict event-driven design:

| Event Source | Event Type | Target Element | Handler Function | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **DOM Engine** | `DOMContentLoaded` | `document` | `init()` | Bootstraps game controller upon document readiness |
| **Numeric Keypad** | `click` | `.key-btn` | `handleDigitInput()` | Captures digit input and updates answer display |
| **Clear Button** | `click` | `#btn-clear-answer` | `handleClearAnswer()` | Resets active digit selection |
| **Submit Button** | `click` | `#btn-submit-answer` | `handleSubmitAnswer()` | Initiates answer validation fetch request |
| **Next Button** | `click` | `#btn-next-question` | `handleNextQuestion()` | Requests next round puzzle from backend |
| **Retry Button** | `click` | `#btn-retry-puzzle` | `fetchPuzzle()` | Re-attempts remote API retrieval upon failure |
| **Physical Keyboard** | `keydown` | `window` | Anonymous listener | Captures keyboard digits (0–9), Backspace, and Enter |

### 3. Separation of Concerns
Event handlers do not perform raw data mutations directly; instead, they alter the application state (`this.state`) and trigger specific view rendering updates (`this.renderPuzzleImage()`, `this.displayFeedback()`).

### 4. Viva Defense (How to explain to your tutor)
> **Tutor Question:** *"What is the advantage of using addEventListener over inline HTML onclick attributes?"*  
> **Student Answer:** *"Inline attributes like `onclick='doSomething()'` violate the separation of concerns by coupling presentation markup with execution logic, and they restrict an element to a single handler. `addEventListener()` cleanly separates JavaScript from HTML, allows multiple decoupled handlers, and supports event bubbling and capturing."*

---

## Theme 4: Virtual Identity

### 1. Theoretical Concept
A **Virtual Identity** represents the digital persona and security credentials of an individual within a computer system. Managing virtual identity requires two distinct mechanisms:
- **Authentication:** Verifying *who* a user claims to be (identity verification).
- **Authorization:** Determining *what* that verified user is permitted to do or access (permission scoping).

### 2. Implementation in this Project

```
[ User Input ] ====> password_hash() ====> Bcrypt Hash Stored in MySQL
                                                    |
[ User Login ] ====> password_verify() ===> Session Regeneration ===> Scoped Data Access
```

1. **Identity Creation (`auth/register.php`):**
   - Unique identity constraints enforced in MySQL on `username` and `email`.
   - Passwords are never stored in plaintext. They are hashed using PHP's native `password_hash($password, PASSWORD_DEFAULT)` which applies industry-standard salted Bcrypt.

2. **Identity Verification (`auth/login.php`):**
   - Password hashes are verified using `password_verify()`, a timing-safe comparison function resilient against side-channel timing attacks.
   - Upon successful verification, `session_regenerate_id(true)` issues a new cryptographic session ID to thwart session fixation exploits.

3. **Session Management & Isolation:**
   - Server-side session state retains identity tokens (`$_SESSION['user_id']`).
   - Session cookies are protected via `httponly: true` (prevents JavaScript cookie theft via XSS) and `samesite: Lax`.
   - Access control (`includes/auth_check.php`) guards protected routes (`dashboard/`, `game/`).

4. **Data Scoping (Authorization):**
   - In `dashboard/index.php` and `game/history.php`, all SQL queries enforce `WHERE user_id = :uid`. A user can only view and manage their own gameplay metrics, upholding strict virtual identity isolation.

5. **Identity Termination (`auth/logout.php`):**
   - Complete purging of `$_SESSION`, cookie invalidation, and server-side session destruction (`session_destroy()`).

### 3. Viva Defense (How to explain to your tutor)
> **Tutor Question:** *"Explain the difference between authentication and authorization in your project."*  
> **Student Answer:** *"Authentication happens in `auth/login.php` where the user proves their identity using their password verified with `password_verify()`. Authorization happens in `includes/auth_check.php` and our database queries: it ensures that an authenticated user can only access protected pages and can exclusively view their own game records in `game_results WHERE user_id = :uid`, preventing horizontal access to other players' scores."*
