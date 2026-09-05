# Multi-Developer & AI Agent Collaboration Protocol

> **CRITICAL RULE FOR ALL DEVELOPERS AND AI AGENTS (LLMs):**  
> **ANY AND EVERY CHANGE MUST ALWAYS BE COMMITTED AND PUSHED TO GITHUB IMMEDIATELY UPON COMPLETION.**

---

## 1. Why This Rule Is Mandatory

This project is actively developed by **two engineers collaborating simultaneously**, with both developers utilizing **AI coding agents (LLMs)** such as Antigravity, Claude Code, and Copilot.

When multiple humans and AI agents modify code concurrently:
- **Unpushed changes cause branch drift**: The other developer's AI agent will read stale remote code, leading to conflicting implementations or duplicated work.
- **Merge conflicts multiply exponentially**: Delaying pushes creates overlapping diffs that waste engineering time.
- **Context loss**: Agents rely on the latest committed codebase to maintain full awareness of project conventions and database state.

---

## 2. Inviolable Rule for Human Developers & AI Agents

> [!IMPORTANT]
> **No task is considered complete until it is committed and pushed to `origin main`.**  
> **Never leave uncommitted or unpushed code on your local environment.**

Every AI agent and developer working on this codebase **MUST** follow this workflow:

### Step 1: Sync Remote State Before Working
Before writing or modifying any code, ensure you have the latest updates from your teammate:
```bash
git pull --rebase origin main
```

### Step 2: Implement Changes & Validate Quality
Make the required edits, then run the test and linter gates:
```bash
# 1. Format PHP code according to project rules
vendor/bin/pint --format agent

# 2. Verify all automated tests pass
php artisan test --compact
```

### Step 3: Commit and Push Immediately
Stage all affected files, create a descriptive commit message, and push directly to remote:
```bash
git add .
git commit -m "feat/fix: concise description of the change"
git push origin main
```

### Step 4: Handle Concurrent Pushes
If your push is rejected because your teammate pushed first:
```bash
git pull --rebase origin main
php artisan test --compact
git push origin main
```

---

## 3. Remote Repository Details
- **Repository URL**: `https://github.com/Spraybery/metonia.git`
- **Primary Branch**: `main`
- **Default Remote**: `origin`

---

## 4. Summary Checklist for Every Task
- [x] Pulled latest changes (`git pull --rebase origin main`)
- [x] PHP formatting verified (`vendor/bin/pint --format agent`)
- [x] Automated tests passing (`php artisan test --compact`)
- [x] Changes committed with a clear message
- [x] **Changes pushed to GitHub (`git push origin main`)**
