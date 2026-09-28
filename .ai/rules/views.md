---
paths:
  - 'resources/views/**'
---

# Views

## Use data-confirm, never window.confirm()
Embedded browsers (e.g. the Claude desktop browser pane) block window.confirm() and it returns false instantly, silently cancelling deletes. For destructive buttons use data-confirm="message" plus data-confirm-form="form-id" (or place the button inside the form); optional data-confirm-label sets the OK button text. The shared modal and click handler live in layouts/master.blade.php.
