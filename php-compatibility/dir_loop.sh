for dir in /var/www/*/; do
  if [ -d "$dir" ]; then # Check if it's actually a directory (and not a file or symlink)
    echo "Processing directory: $dir"
    filename="${dir//\//_}"
    vendor/bin/phpcs -n -p ${dir} --standard=PHPCompatibility --runtime-set testVersion 8.2 > "phpcs${filename}.log"
    # Add your commands to execute within each directory here
  fi
done