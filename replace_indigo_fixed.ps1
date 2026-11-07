# PowerShell script to replace indigo Tailwind classes with teal equivalents
# Adjust as needed for the project's color palette.
$root = 'd:/Project UKK/belajar_laravel_handa'
Get-ChildItem -Path $root -Recurse -Include *.blade.php | ForEach-Object {
    $path = $_.FullName
    $content = Get-Content -Path $path -Raw
    $newContent = $content `
        -replace 'bg-indigo-600', 'bg-teal-600' `
        -replace 'bg-indigo-700', 'bg-teal-700' `
        -replace 'bg-indigo-500', 'bg-teal-500' `
        -replace 'bg-indigo-50',  'bg-teal-50' `
        -replace 'text-indigo-600', 'text-teal-600' `
        -replace 'text-indigo-700', 'text-teal-700' `
        -replace 'text-indigo-500', 'text-teal-500' `
        -replace 'text-indigo-400', 'text-teal-400' `
        -replace 'text-indigo-300', 'text-teal-300' `
        -replace 'text-indigo-200', 'text-teal-200' `
        -replace 'text-indigo-100', 'text-teal-100' `
        -replace 'border-indigo-600', 'border-teal-600' `
        -replace 'border-indigo-500', 'border-teal-500' `
        -replace 'border-indigo-400', 'border-teal-400' `
        -replace 'border-indigo-300', 'border-teal-300' `
        -replace 'focus:ring-indigo-500', 'focus:ring-teal-500' `
        -replace 'focus:ring-indigo-400', 'focus:ring-teal-400' `
        -replace 'hover:bg-indigo-600', 'hover:bg-teal-600' `
        -replace 'hover:bg-indigo-700', 'hover:bg-teal-700' `
        -replace 'hover:text-indigo-600', 'hover:text-teal-600' `
        -replace 'hover:text-indigo-700', 'hover:text-teal-700'
    if ($newContent -ne $content) {
        Set-Content -Path $path -Value $newContent -Encoding UTF8
        Write-Host "Updated $path"
    }
}
