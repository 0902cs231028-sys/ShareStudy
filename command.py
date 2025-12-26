import os
import subprocess
from datetime import datetime

# --- CONFIGURATION ---
CHANGELOG_FILE = "CHANGELOG.md"

# --- SMART CONTEXT MAP ---
# Detects what you worked on based on filenames
CONTEXT_MAP = {
    "admin": "🛡️ Admin Panel",
    "css": "🎨 UI/UX Design",
    "style.css": "🎨 Visual Styles",
    "chat": "💬 Chat Engine",
    "upload": "📂 File System",
    "db.sql": "🗄️ Database Schema",
    "login": "🔐 Authentication",
    "register": "🔐 User Onboarding",
    "assets": "🖼️ Assets",
    "includes": "⚙️ Configuration",
    "dashboard": "🏠 Dashboard",
}

def run_command(command):
    return subprocess.run(command, shell=True, capture_output=True, text=True).stdout.strip()

def get_commit_changes():
    # Git command to see files changed in the HEAD commit
    output = run_command("git diff-tree --no-commit-id --name-status -r HEAD")
    
    changes = []
    if not output:
        return changes

    for line in output.split("\n"):
        parts = line.split()
        if len(parts) < 2: continue
        
        status = parts[0]
        filepath = parts[1]
        filename = os.path.basename(filepath)

        # Ignore the changelog itself and the script
        if filename in [CHANGELOG_FILE, "command.py", ".github"]:
            continue

        # Determine Action
        action = "Updated"
        if status.startswith("A"): action = "New Feature Added"
        elif status.startswith("D"): action = "Removed"
        elif status.startswith("M"): action = "Enhanced"

        # Determine Context
        context = "General Tweaks"
        for key, value in CONTEXT_MAP.items():
            if key in filepath:
                context = value
                break
        
        changes.append(f"- **{context}:** {action} -> `{filename}`")
    
    return changes

def update_changelog(changes):
    if not changes:
        print("No significant changes found in this commit.")
        return False

    date_str = datetime.now().strftime("%Y-%m-%d")
    # A generic header, or you can try to fetch the commit message if you want
    new_entry = f"\n## [Update] - {date_str}\n" + "\n".join(changes) + "\n"

    content = ""
    if os.path.exists(CHANGELOG_FILE):
        with open(CHANGELOG_FILE, "r") as f:
            content = f.read()
    else:
        content = "# 🔄 Changelog\n\n"

    # Insert after the main title
    # If using the format '# 🔄 Changelog', we append after the first double newline
    if "\n\n" in content:
        parts = content.split("\n\n", 1)
        final_content = parts[0] + "\n" + new_entry + "\n" + parts[1]
    else:
        final_content = content + new_entry

    with open(CHANGELOG_FILE, "w") as f:
        f.write(final_content)
    
    return True

if __name__ == "__main__":
    print("🤖 Analyzing commit...")
    changes = get_commit_changes()
    
    if update_changelog(changes):
        print(f"✅ {CHANGELOG_FILE} updated.")
        # We allow the GitHub Action to handle the git push part
    else:
        print("💤 No changes to record.")
