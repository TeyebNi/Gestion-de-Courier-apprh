@echo off
:: ============================================================
:: A executer UNE SEULE FOIS, directement SUR LE SERVEUR
:: (192.168.1.150), APRES avoir copie l'application dans
:: C:\xampp\htdocs\Gestion-de-Courier-apprh sur ce serveur.
::
:: Ce script :
:: 1) Genere un certificat auto-signe pour "courrier" (valable
::    10 ans) DIRECTEMENT sur ce serveur (la cle privee ne
::    quitte jamais cette machine).
:: 2) Ajoute un VirtualHost HTTPS (443) pour "courrier" dans
::    httpd-vhosts.conf, a cote du VirtualHost HTTP existant.
::
:: Pre-requis : XAMPP installe dans C:\xampp sur CE serveur,
:: avec le VirtualHost "courrier" (port 80) deja configure
:: comme sur le poste de developpement.
:: ============================================================

set XAMPP=C:\xampp
set APACHE=%XAMPP%\apache

net session >nul 2>&1
if %errorLevel% NEQ 0 (
    echo Ce script doit etre execute en tant qu'administrateur.
    echo Clic droit sur ce fichier, puis "Executer en tant qu'administrateur".
    pause
    exit /b 1
)

if not exist "%APACHE%\bin\openssl.exe" (
    echo ERREUR : openssl introuvable dans %APACHE%\bin
    echo Verifiez que XAMPP est bien installe dans %XAMPP% sur ce serveur.
    pause
    exit /b 1
)

if exist "%APACHE%\conf\ssl.crt\courrier.crt" (
    echo Un certificat courrier.crt existe deja sur ce serveur.
    echo Suppression et regeneration...
)

echo Generation du certificat pour "courrier"...
(
    echo [req]
    echo distinguished_name = req_distinguished_name
    echo x509_extensions = v3_req
    echo prompt = no
    echo.
    echo [req_distinguished_name]
    echo CN = courrier
    echo.
    echo [v3_req]
    echo keyUsage = keyEncipherment, dataEncipherment, digitalSignature
    echo extendedKeyUsage = serverAuth
    echo subjectAltName = @alt_names
    echo.
    echo [alt_names]
    echo DNS.1 = courrier
    echo DNS.2 = localhost
    echo IP.1 = 127.0.0.1
    echo IP.2 = 192.168.1.150
) > "%TEMP%\courrier-ssl.cnf"

"%APACHE%\bin\openssl.exe" req -x509 -nodes -days 3650 -newkey rsa:2048 ^
    -keyout "%APACHE%\conf\ssl.key\courrier.key" ^
    -out "%APACHE%\conf\ssl.crt\courrier.crt" ^
    -config "%TEMP%\courrier-ssl.cnf" -extensions v3_req

if %errorLevel% NEQ 0 (
    echo ERREUR lors de la generation du certificat.
    pause
    exit /b 1
)

echo.
echo Certificat genere : %APACHE%\conf\ssl.crt\courrier.crt
echo (le fichier .key reste sur ce serveur, ne le copiez jamais ailleurs)

findstr /C:"VirtualHost \*:443" "%APACHE%\conf\extra\httpd-vhosts.conf" | findstr /C:"courrier" >nul 2>&1
findstr /C:"ServerName courrier" "%APACHE%\conf\extra\httpd-vhosts.conf" >nul
echo.
echo ============================================================
echo Ajoutez maintenant CE BLOC a la fin de :
echo   %APACHE%\conf\extra\httpd-vhosts.conf
echo (sauf s'il y est deja) :
echo ============================================================
echo.
echo # Gestion de Courrier : https://courrier
echo ^<VirtualHost *:443^>
echo     ServerName courrier
echo     DocumentRoot "C:/xampp/htdocs/Gestion-de-Courier-apprh/public"
echo     ^<Directory "C:/xampp/htdocs/Gestion-de-Courier-apprh/public"^>
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo     SSLEngine on
echo     SSLCertificateFile "conf/ssl.crt/courrier.crt"
echo     SSLCertificateKeyFile "conf/ssl.key/courrier.key"
echo     ErrorLog "logs/courrier-ssl-error.log"
echo     CustomLog "logs/courrier-ssl-access.log" common
echo ^</VirtualHost^>
echo.
echo Puis : redemarrez Apache depuis XAMPP Control Panel.
echo.
echo Enfin : copiez %APACHE%\conf\ssl.crt\courrier.crt (le fichier
echo .crt UNIQUEMENT, jamais le .key) vers le dossier
echo "Installation-Courrier" utilise sur chaque poste client,
echo puis relancez install-courrier-access.bat sur chaque poste.
echo ============================================================
pause
