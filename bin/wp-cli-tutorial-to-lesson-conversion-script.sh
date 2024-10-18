#!/bin/bash

# Function to display usage information
usage() {
    echo "Usage: $0 [-d] (-u <post_url> | -f <file_path>)"
    echo "  -d    Dry run mode (don't make any changes)"
    echo "  -u    Single post URL"
    echo "  -f    File containing list of URLs (one per line)"
    exit 1
}

# Function to check if a command was successful
check_success() {
    if [ $? -ne 0 ]; then
        echo "Error: $1"
        return 1
    fi
    return 0
}

# Function to process a single URL
process_url() {
    local post_url=$1
    local dry_run=$2

    # Extract post ID from URL
    post_id=$(wp post list --post_type=wporg_workshop --field=ID --format=ids --search="$post_url")
    if ! check_success "Failed to find post with URL: $post_url"; then
        return 1
    fi

    echo "Found post with ID: $post_id"

    # Change post type directly
    if [ "$dry_run" = true ]; then
        echo "Dry run: Would change post type of post (ID: $post_id) to 'lesson'"
    else
        wp post update $post_id --post_type=lesson
        if ! check_success "Failed to change post type to lesson"; then
            return 1
        fi
        echo "Changed post type of post (ID: $post_id) to 'lesson'"
    fi

    echo "Processing completed for URL: $post_url"
    return 0
}

# Parse command line arguments
dry_run=false
url_mode=""
while getopts ":du:f:" opt; do
    case ${opt} in
        d )
            dry_run=true
            ;;
        u )
            url_mode="single"
            post_url=$OPTARG
            ;;
        f )
            url_mode="file"
            file_path=$OPTARG
            ;;
        \? )
            usage
            ;;
    esac
done

# Check if a URL or file was provided
if [ -z "$url_mode" ]; then
    usage
fi

# Process URLs
if [ "$url_mode" = "single" ]; then
    process_url "$post_url" "$dry_run"
elif [ "$url_mode" = "file" ]; then
    if [ ! -f "$file_path" ]; then
        echo "Error: File not found: $file_path"
        exit 1
    fi

    while IFS= read -r url || [[ -n "$url" ]]; do
        process_url "$url" "$dry_run"
    done < "$file_path"
fi

echo "All operations completed"
