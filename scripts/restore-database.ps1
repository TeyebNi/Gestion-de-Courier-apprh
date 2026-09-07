# ============================================================
# Script de restauration de la base de donnees
# Gestion de Courier - dbcuourier
# ============================================================

$MysqlPath  = "C:\xampp\mysql\bin\mysql.exe"
$DbName     = "dbcuourier"
$DbUser     = "root"
$DbPassword = ""
$BackupDir  = "C:\xampp\htdocs\Gestion-de-Courier-apprh\storage\app\backups"

$backups = Get-ChildItem -Path $BackupDir -Filter "dbcuourier_*.sql" | Sort-Object LastWriteTime -Descending

if ($backups.Count -eq 0) {
    Write-Host "Aucune sauvegarde trouvee dans $BackupDir" -ForegroundColor Red
    exit 1
}

Write-Host "Sauvegardes disponibles :"
for ($i = 0; $i -lt $backups.Count; $i++) {
    Write-Host "[$i] $($backups[$i].Name)  ($($backups[$i].LastWriteTime))"
}

$choice = Read-Host "Entrez le numero de la sauvegarde a restaurer"
$selected = $backups[[int]$choice]

if (-not $selected) {
    Write-Host "Choix invalide." -ForegroundColor Red
    exit 1
}

Write-Host "ATTENTION : ceci va ECRASER toutes les donnees actuelles de '$DbName' !" -ForegroundColor Yellow
$confirm = Read-Host "Tapez OUI pour confirmer"

if ($confirm -ne "OUI") {
    Write-Host "Annule."
    exit 0
}

if ($DbPassword -eq "") {
    Get-Content $selected.FullName | & $MysqlPath -u $DbUser $DbName
} else {
    Get-Content $selected.FullName | & $MysqlPath -u $DbUser -p$DbPassword $DbName
}

Write-Host "Restauration terminee a partir de $($selected.Name)" -ForegroundColor Green
