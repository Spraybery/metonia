# Multi-Developer & AI Agent Collaboration Protocol

> **CRITICAL RULE FOR ALL DEVELOPERS AND AI AGENTS (LLMs):**  
> **ANY AND EVERY CHANGE MUST ALWAYS BE COMMITTED AND PUSHED TO GITHUB IMMEDIATELY UPON COMPLETION. NEVER LEAVE WORK UNPUSHED.**

---

## 1. Why This Protocol Is Mandatory

This repository is developed concurrently by **two human engineers**, both leveraging **AI coding agents (LLMs)** such as Antigravity, Claude Code, and GitHub Copilot.

Without strict branching and pushing protocols:
- **Unpushed code causes branch drift**: Teammates and their AI agents work against outdated remote code.
- **Merge conflicts multiply**: Concurrent changes to identical files lead to complex merge resolutions.
- **Context hallucination**: LLMs lose situational awareness if the remote repository does not reflect the latest state.

---

## 2. Branching Strategy

To isolate work and eliminate collision between developers and AI agents, all feature development, major refactors, and complex fixes must use **dedicated feature branches**.

### 2.1 Branch Naming Conventions
Always use lowercase hyphenated branch names prefixed with the category:

| Type | Pattern | Example | Use Case |
|---|---|---|---|
| **Features** | `feat/<name-or-task>` | `feat/delan-supplier-reports`<br>`feat/inventory-barcodes` | New screens, models, APIs, and workflows |
| **Bug Fixes** | `fix/<bug-description>` | `fix/stock-decrement-rounding`<br>`fix/login-session-redirect` | Fixing bugs, errors, or calculation issues |
| **Refactoring** | `refactor/<scope>` | `refactor/vehicle-parts-modal` | Code cleanup, optimization, or architecture tweaks |
| **Documentation** | `docs/<topic>` | `docs/api-specification` | Updates to guides, README, or technical specifications |

---

## 3. Step-by-Step Branch Lifecycle

### Step 1: Create a Feature Branch from Fresh `main`
Always pull the latest remote changes before creating your branch:
```bash
git checkout main
git pull --rebase origin main
git checkout -b feat/your-feature-name
```

### Step 2: Publish Your Branch to Remote Immediately
Publish the new branch so your teammate and other AI agents can see active work:
```bash
git push -u origin feat/your-feature-name
```

### Step 3: Incremental Development & Immediate Push
As you or your AI agent complete each modification or milestone:
```bash
# 1. Format code
vendor/bin/pint --format agent

# 2. Verify tests pass
php artisan test --compact

# 3. Commit and push immediately to your remote branch
git add .
git commit -m "feat: descriptive message of the change"
git push origin feat/your-feature-name
```

> [!IMPORTANT]
> **Do NOT wait until the end of the day or until an entire module is done to push.**  
> Commit and push each completed subtask to GitHub immediately.

### Step 4: Keep Your Branch Synchronized with `main`
If your teammate pushes changes to `main` while you are working on your branch:
```bash
git fetch origin
git rebase origin/main
php artisan test --compact
git push --force-with-lease origin feat/your-feature-name
```

### Step 5: Merge into `main` and Push
Once the feature is finished and all tests pass:

#### Option A: Direct Fast-Forward Merge (Standard)
```bash
git checkout main
git pull --rebase origin main
git merge --ff-only feat/your-feature-name
php artisan test --compact
git push origin main

# Delete the local & remote feature branch after merge
git branch -d feat/your-feature-name
git push origin --delete feat/your-feature-name
```

#### Option B: GitHub Pull Request (PR)
1. Push branch to GitHub: `git push origin feat/your-feature-name`.
2. Open a Pull Request on GitHub.
3. Review and merge the PR into `main`.
4. Locally update `main`:
   ```bash
   git checkout main
   git pull --rebase origin main
   git branch -d feat/your-feature-name
   ```

---

## 4. When Direct Commits to `main` Are Allowed
Direct commits to `main` are permitted **only** for:
- Minor documentation fixes (e.g. `README.md`, comments).
- Single-line configuration or environment tweaks.
- Urgent zero-risk hotfixes.

Even for direct `main` commits, you must run `git pull --rebase origin main` first, verify `php artisan test --compact`, commit, and **immediately `git push origin main`**.

---

## 5. Summary Checklist for Every Task
- [x] Started from latest `main` (`git pull --rebase origin main`)
- [x] Working on a dedicated branch (`feat/...` or `fix/...`)
- [x] Branch published to remote (`git push -u origin <branch>`)
- [x] Code formatted (`vendor/bin/pint --format agent`)
- [x] Automated tests verified (`php artisan test --compact`)
- [x] Changes committed with a clear message
- [x] **Pushed immediately to GitHub (`git push origin <branch>`)**
- [x] Merged to `main` and pushed to `origin main` upon completion
