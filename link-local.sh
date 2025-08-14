#!/bin/bash

# Configuration
LOCAL_SITE_NAME="sheyifunmi"
LOCAL_BASE="$HOME/Local Sites/$LOCAL_SITE_NAME/app"

# Array of items to symlink (repo_path:local_path)
LINKS=(
    "web/wp-content/themes/honeycom3:public/wp-content/themes/honeycom3"
    "web/wp-content/mu-plugins:public/wp-content/mu-plugins"
)

echo "Starting symlink process for $LOCAL_SITE_NAME..."

for link in "${LINKS[@]}"; do
    IFS=':' read -r repo_path local_path <<< "$link"
    
    full_local_path="$LOCAL_BASE/$local_path"
    full_repo_path="$(pwd)/$repo_path"
    
    # Check if repo path exists
    if [ ! -e "$full_repo_path" ]; then
        echo "Warning: $repo_path does not exist in repository"
        continue
    fi
    
    # Create parent directory if it doesn't exist
    parent_dir=$(dirname "$full_local_path")
    if [ ! -d "$parent_dir" ]; then
        echo "Creating parent directory: $parent_dir"
        mkdir -p "$parent_dir"
    fi
    
    # Remove existing if present
    if [ -e "$full_local_path" ]; then
        echo "Removing existing: $full_local_path"
        rm -rf "$full_local_path"
    fi
    
    # Create symlink
    ln -s "$full_repo_path" "$full_local_path"
    echo "✓ Linked: $repo_path -> $local_path"
done

echo "Symlink process completed!"