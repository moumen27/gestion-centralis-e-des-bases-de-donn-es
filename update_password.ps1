$files = @(
    "acceuil.php",
    "acceuiluser.php",
    "adduser.php",
    "delete.php",
    "display_database.php",
    "displayall.php",
    "displayalluser.php",
    "displaying.php",
    "displayinguser.php",
    "failure.php",
    "modifier.php",
    "restore.php",
    "restorelist.php",
    "restorelistuser.php",
    "restoreshit.php",
    "sauvegarde.php",
    "sauvegardeuser.php",
    "saves.php",
    "signup.php",
    "success.php",
    "successuser.php",
    "update_autorisation.php",
    "userinf.php",
    "userinf_action.php",
    "usersprofiles.php"
)

$sourceDir = "C:\Users\PC-CLICK-PLUS\Downloads\SEAAL\SEAAL"
$targetDir = "C:\xampp\htdocs\SEAAL"

foreach ($file in $files) {
    $sourcePath = Join-Path $sourceDir $file
    $targetPath = Join-Path $targetDir $file
    
    if (Test-Path $sourcePath) {
        $content = Get-Content $sourcePath -Raw
        $content = $content -replace '\$password = "";', '$password = "776A3kXss";'
        Set-Content $sourcePath -Value $content -NoNewline
        
        # Copy to htdocs
        Copy-Item $sourcePath $targetPath -Force
        Write-Host "Updated: $file" -ForegroundColor Green
    }
}

Write-Host "`nAll files updated successfully!" -ForegroundColor Cyan
Write-Host "Password changed to: 776A3kXss" -ForegroundColor Yellow
