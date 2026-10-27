# Script pour corriger tous les chemins absolus en chemins relatifs
$sourceDir = "C:\Users\PC-CLICK-PLUS\Downloads\SEAAL\SEAAL"
$targetDir = "C:\xampp\htdocs\SEAAL"

# Liste des fichiers PHP
$phpFiles = Get-ChildItem -Path $sourceDir -Filter "*.php"

$count = 0
foreach ($file in $phpFiles) {
    $filePath = $file.FullName
    $content = Get-Content $filePath -Raw -Encoding UTF8
    
    $modified = $false
    
    # Correction des redirections
    if ($content -match 'header\("Location: /') {
        $content = $content -replace 'header\("Location: /', 'header("Location: '
        $modified = $true
    }
    
    # Correction des liens href
    if ($content -match 'href="/(?!http)') {
        $content = $content -replace 'href="/(?!http)', 'href="'
        $modified = $true
    }
    
    # Correction des actions
    if ($content -match 'action="/(?!http)') {
        $content = $content -replace 'action="/(?!http)', 'action="'
        $modified = $true
    }
    
    if ($modified) {
        Set-Content $filePath -Value $content -NoNewline -Encoding UTF8
        $targetPath = Join-Path $targetDir $file.Name
        Copy-Item $filePath $targetPath -Force
        Write-Host "Corrige: $($file.Name)" -ForegroundColor Green
        $count++
    }
}

Write-Host ""
Write-Host "$count fichiers corriges" -ForegroundColor Cyan
