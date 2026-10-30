[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$RepositoryPath,

    [Parameter(Mandatory = $true)]
    [string]$BundlePath
)

$ErrorActionPreference = 'Stop'

function Invoke-Git {
    param([Parameter(ValueFromRemainingArguments = $true)][string[]]$GitArgs)
    & git @GitArgs
    if ($LASTEXITCODE -ne 0) {
        throw "Git command failed (exit $LASTEXITCODE): git $($GitArgs -join ' ')"
    }
}

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw 'Git was not found. Install Git for Windows and reopen PowerShell.'
}

$repo = (Resolve-Path -LiteralPath $RepositoryPath).Path
$bundle = (Resolve-Path -LiteralPath $BundlePath).Path

if (-not (Test-Path -LiteralPath (Join-Path $repo '.git'))) {
    throw "RepositoryPath is not a Git working tree: $repo"
}

Push-Location $repo
try {
    $top = (& git rev-parse --show-toplevel).Trim()
    if ($LASTEXITCODE -ne 0 -or $top -ne $repo) {
        throw 'Repository path validation failed.'
    }

    $branch = (& git branch --show-current).Trim()
    if ($branch -ne 'main') {
        throw "Expected branch 'main', found '$branch'. Switch to main before publishing."
    }

    $origin = (& git remote get-url origin).Trim()
    if ($LASTEXITCODE -ne 0 -or $origin -notmatch '^https://github\.com/daniyal3y/Programing-web(?:\.git)?$') {
        throw "Unexpected origin URL: $origin"
    }

    $changes = (& git status --porcelain).Trim()
    if ($LASTEXITCODE -ne 0 -or $changes) {
        throw 'Working tree is not clean. Commit or stash local changes before publishing.'
    }

    if (Test-Path -LiteralPath (Join-Path $repo 'raffle-link-platform')) {
        throw 'raffle-link-platform folder already exists. Refusing to overwrite existing files.'
    }

    Write-Host 'Fetching the project bundle and its original commit history...' -ForegroundColor Cyan
    Invoke-Git fetch --no-tags $bundle 'main:refs/heads/raffle-source/main'

    Write-Host 'Adding the application under raffle-link-platform/...' -ForegroundColor Cyan
    Invoke-Git subtree add --prefix=raffle-link-platform raffle-source/main

    Write-Host 'Pushing main to GitHub...' -ForegroundColor Cyan
    Invoke-Git push -u origin main

    Write-Host 'Published successfully:' -ForegroundColor Green
    Write-Host 'https://github.com/daniyal3y/Programing-web/tree/main/raffle-link-platform'
    Write-Host 'The application history is preserved in the repository.' -ForegroundColor Green
}
finally {
    Pop-Location
}
