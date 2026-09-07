# ============================================================
# Script de sauvegarde automatique de la base de donnees
# Gestion de Courier - dbcuourier
# ============================================================

# --- CONFIGURATION (a adapter si besoin) ---
$MysqlDumpPath = "C:\xampp\mysql\bin\mysqldump.exe"
$DbName        = "dbcuourier"
$DbUser        = "root"
$DbPassword    = ""   # laisser vide si pas de mot de passe root
$BackupDir     = "C:\xampp\htdocs\Gestion-de-Courier-apprh\storage\app\backups"
$KeepDays      = 30    # nombre de jours a conserver avant suppression automatique
$LogFile       = "C:\xampp\htdocs\Gestion-de-Courier-apprh\storage\app\backups\backup.log"

# --- Ne pas modifier en dessous de cette ligne ---

function Write-Log {
    param($Message)
    $line = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - $Message"
    Add-Content -Path $LogFile -Value $line
    Write-Host $Message
}

# Cree le dossier de sauvegarde s'il n'existe pas
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

Write-Log "===== Debut de la sauvegarde ====="
Write-Log "Utilisateur execution : $env:USERNAME"
Write-Log "Dossier de travail : $(Get-Location)"

if (-not (Test-Path $MysqlDumpPath)) {
    Write-Log "ERREUR : mysqldump introuvable a $MysqlDumpPath"
    exit 1
}

$Timestamp  = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$BackupFile = Join-Path $BackupDir "dbcuourier_$Timestamp.sql"

Write-Log "Sauvegarde de la base '$DbName' en cours..."

try {
    if ($DbPassword -eq "") {
        & $MysqlDumpPath -u $DbUser $DbName 2>> $LogFile > $BackupFile
    } else {
        & $MysqlDumpPath -u $DbUser -p$DbPassword $DbName 2>> $LogFile > $BackupFile
    }
    $ExitCode = $LASTEXITCODE
} catch {
    Write-Log "EXCEPTION : $($_.Exception.Message)"
    exit 1
}

Write-Log "Code de sortie mysqldump : $ExitCode"

if ($ExitCode -eq 0 -and (Test-Path $BackupFile) -and (Get-Item $BackupFile).Length -gt 0) {
    Write-Log "Sauvegarde reussie : $BackupFile ($((Get-Item $BackupFile).Length) octets)"
} else {
    Write-Log "ECHEC de la sauvegarde !"
    if (Test-Path $BackupFile) { Remove-Item $BackupFile -Force }
    exit 1
}

# Supprime les sauvegardes plus vieilles que $KeepDays jours
$CutoffDate = (Get-Date).AddDays(-$KeepDays)
Get-ChildItem -Path $BackupDir -Filter "dbcuourier_*.sql" |
    Where-Object { $_.LastWriteTime -lt $CutoffDate } |
    ForEach-Object {
        Write-Host "Suppression de l'ancienne sauvegarde : $($_.Name)"
        Remove-Item $_.FullName -Force
    }

Write-Host "Termine."
