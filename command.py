import os
import subprocess
import re
from datetime import datetime

# --- CONFIGURATION ---
CHANGELOG_FILE = "CHANGELOG.md"

# --- SMART PATTERNS ---
# Regex to detect specific code changes in PHP/JS/CSS
PATTERNS = [
    (r'^\+\s*function\s+([a-zA-Z0-9_]+)', "✨ Added function `{}`"),
    (r'^\-\s*function\s+([a-zA-Z0-9_]+)', "🔥 Removed function `{}`"),
    (r'^\+\s*class\s+([a-zA-Z0-9_]+)', "📦 Created class `{}`"),
    (r'^\+\s*CREATE TABLE\s+`?([a-zA-Z0-9_]+)`?', "🗄️ Created Database Table `{}`"),
    (r'^\+\s*ALTER TABLE\s+`?([a-zA-Z0-9_]+)`?', "⚠️ Modified Schema for `{}`"),
    (r'^\+\s*(public|private|protected)\s+\$([a-zA-Z0-9_]+)', "🔹 Added property `${}`"),
    (r'password_verify|password_hash|session_start', "🔒 Security/Auth Logic"),
    (r'btn-|glass-panel|css', "🎨 UI Visual Update"),
]

# --- FILE CONTEXT MAP ---
CONTEXT_MAP = {
    "admin": "🛡️ Admin Core",
    "css": "🎨 Design System",
    "chat": "💬 Chat Engine",
    "upload": "📂 File Engine",
    "db.sql": "🗄️ Database",
    "login": "🔐 Auth",
    "register": "🔐 Auth",
    "assets": "🖼️ Assets",
}

def run_command(command):
    return subprocess.run(command, shell=True, capture_output=True, text=True).stdout.strip()

def analyze_file_diff(filename):
    """Reads the actual code changes for a file to guess what happened."""
    # Get the diff (lines added/removed)
    diff = run_command(f"git diff HEAD~1 HEAD -- {filename}")
    
    details = []
    
    # 1. Check for specific code patterns (Functions, Classes, SQL)
    for pattern, message_template in PATTERNS:
        matches = re.findall(pattern, diff, re.MULTILINE)
        for match in matches:
            # If match is a tuple (regex groups), take the first meaningful part
            item_name = match[0] if isinstance(match, tuple) else match
            # Avoid duplicating generic messages
            if "{}" in message_template:
                details.append(message_template.format(item_name))
            elif message_template not in details:
                details.append(message_template)

    # 2. Heuristics (Guessing based on volume of changes)
    added_lines = len(re.findall(r'^\+', diff, re.MULTILINE))
    removed_lines = len(re.findall(r'^\-', diff, re.MULTILINE))

    if not details:
        if added_lines > 10 and removed_lines < 2:
            details.append("✨ Major Feature Implementation")
        elif removed_lines > 10 and added_lines < 2:
            details.append("🔥 Cleanup / Refactoring")
        elif "fix" in filename.lower() or "error" in diff.lower():
            details.append("🐛 Bug Fix")
        else:
            details.append("⚡ Logic Optimization")

    return list(set(details))  # Remove duplicates

def get_smart_changes():
    output = run_command("git diff-tree --no-commit-id --name-status -r HEAD")
    changes = []
    
    if not output: return changes

    for line in output.split("\n"):
        parts = line.split()
        if len(parts) < 2: continue
        
        status, filepath = parts[0], parts[1]
        filename = os.path.basename(filepath)

        if filename in [CHANGELOG_FILE, "command.py", ".github"]: continue

        # 1. Determine Context
        context = "🔧 General"
        for key, val in CONTEXT_MAP.items():
            if key in filepath:
                context = val
                break

        # 2. Determine Action
        if status.startswith("A"):
            entry = f"- **{context}:** 🎉 Created `{filename}`"
        elif status.startswith("D"):
            entry = f"- **{context}:** 🗑️ Deleted `{filename}`"
        else:
            # Deep Scan the file changes
            insights = analyze_file_diff(filename)
            desc = ", ".join(insights[:2]) # Take top 2 insights
            entry = f"- **{context}:** {desc} in `{filename}`"
        
        changes.append(entry)
    
    return changes

def update_changelog(changes):
    if not changes: return False

    date_str = datetime.now().strftime("%Y-%m-%d")
    new_entry = f"\n## [Auto-Analysis] - {date_str}\n" + "\n".join(changes) + "\n"

    content = ""
    if os.path.exists(CHANGELOG_FILE):
        with open(CHANGELOG_FILE, "r") as f: content = f.read()
    else:
        content = "# 🔄 Changelog\n\n"

    if "\n\n" in content:
        parts = content.split("\n\n", 1)
        final_content = parts[0] + "\n" + new_entry + "\n" + parts[1]
    else:
        final_content = content + new_entry

    with open(CHANGELOG_FILE, "w") as f: f.write(final_content)
    return True

if __name__ == "__main__":
    print("🧠 Starting Smart Analysis...")
    changes = get_smart_changes()
    if update_changelog(changes):
        print(f"✅ Enhanced {CHANGELOG_FILE} with code insights.")
    else:
        print("💤 No content to document.")
