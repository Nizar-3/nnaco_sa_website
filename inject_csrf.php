<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/app/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$injectedCount = 0;

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Check if form with POST exists and doesn't have CSRF token yet
    if (stripos($content, '<form') !== false && stripos($content, 'method="post"') !== false) {
        if (strpos($content, 'name="csrf_token"') === false && strpos($content, "name='csrf_token'") === false) {
            
            // Replace <form ... > with <form ... >\n<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
            // We can't use [^>]+ because of <?= ... ?> containing >. 
            // Instead, let's use a simpler approach: find '<form' and then find the next '>', keeping track of PHP tags if possible, 
            // but actually, we can just look for 'method="post"' or 'method="POST"' and insert after the closing '>' of that form tag.
            
            // Better regex: match <form ...> non-greedy, ensuring it has method="post"
            // Since we know the codebase, we can just replace the specific form lines.
            
            $newContent = preg_replace_callback('/(<form\b[^>]*?(?:>|<\?php.*?\?>[^>]*?>|<\?=.*?\?>[^>]*?>))/is', function($matches) {
                $tag = $matches[1];
                if (stripos($tag, 'method="post"') !== false || stripos($tag, "method='post'") !== false) {
                    return $tag . "\n<input type=\"hidden\" name=\"csrf_token\" value=\"<?= CSRF::generateToken(); ?>\">";
                }
                return $tag;
            }, $content);
            
            if ($newContent !== $content) {
                file_put_contents($path, $newContent);
                $injectedCount++;
                echo "Successfully injected into: $path\n";
            } else {
                echo "Failed to inject (regex didn't match) into: $path\n";
            }
        } else {
            echo "Already injected: $path\n";
        }
    }
}
echo "Total files injected: $injectedCount\n";
