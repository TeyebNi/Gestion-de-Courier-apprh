@echo off
:: ============================================================
:: Installe l'acces a "https://courrier" sur CE poste :
:: 1) Ajoute "courrier" au fichier hosts (pointe vers le serveur)
:: 2) Installe le certificat comme autorite de confiance
::    (supprime l'avertissement "Non securise" dans Chrome/Edge/Brave)
::
:: A copier avec courrier.crt (meme dossier), puis executer ce
:: fichier en tant qu'administrateur sur chaque poste qui doit
:: acceder au site.
:: ============================================================

set SERVER_IP=192.168.1.150
set HOSTS_FILE=%SystemRoot%\System32\drivers\etc\hosts

net session >nul 2>&1
if %errorLevel% NEQ 0 (
    echo Ce script doit etre execute en tant qu'administrateur.
    echo Clic droit sur ce fichier, puis "Executer en tant qu'administrateur".
    pause
    exit /b 1
)

if not exist "%~dp0courrier.crt" (
    echo ERREUR : courrier.crt est introuvable dans ce dossier.
    echo Copiez courrier.crt a cote de ce script avant de l'executer.
    pause
    exit /b 1
)

findstr /C:"courrier" "%HOSTS_FILE%" >nul
if %errorLevel% EQU 0 (
    echo Une entree "courrier" existe deja dans le fichier hosts.
    echo Verifiez qu'elle pointe bien vers %SERVER_IP% si le site ne repond pas.
) else (
    echo %SERVER_IP%    courrier>> "%HOSTS_FILE%"
    echo Entree ajoutee au fichier hosts : %SERVER_IP% courrier
)

echo Installation du certificat de confiance...
certutil -addstore "Root" "%~dp0courrier.crt"

echo.
echo ============================================================
echo Termine.
echo Fermez COMPLETEMENT votre navigateur (Chrome/Edge/Brave) puis
echo rouvrez-le et allez sur https://courrier
echo.
echo Si vous utilisez Firefox : Firefox a son propre magasin de
echo certificats, ce script ne suffit pas pour lui. Voir les
echo instructions manuelles fournies separement pour Firefox.
echo ============================================================
pause
