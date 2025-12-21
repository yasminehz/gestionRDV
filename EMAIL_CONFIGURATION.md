# Configuration de l'envoi d'emails

## Fonctionnalité
Le système envoie automatiquement un email au patient lorsque son rendez-vous est **confirmé** (validé) par le médecin ou l'assistant.

## Configuration du MAILER_DSN

### Option 1 : Mailhog (recommandé pour le développement)
1. Installer Mailhog :
   ```powershell
   # Via Chocolatey
   choco install mailhog
   
   # Ou télécharger depuis https://github.com/mailhog/MailHog/releases
   ```

2. Lancer Mailhog :
   ```powershell
   mailhog
   ```

3. Dans `.env`, utiliser :
   ```
   MAILER_DSN=smtp://localhost:1025
   ```

4. Accéder à l'interface web : http://localhost:8025

### Option 2 : Mailtrap (tests en ligne)
1. Créer un compte sur https://mailtrap.io
2. Récupérer les identifiants SMTP
3. Dans `.env`, utiliser :
   ```
   MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
   ```

### Option 3 : Gmail (production)
1. Activer l'authentification à 2 facteurs sur votre compte Gmail
2. Générer un mot de passe d'application : https://myaccount.google.com/apppasswords
3. Dans `.env`, utiliser :
   ```
   MAILER_DSN=gmail+smtp://votre-email@gmail.com:votre-mot-de-passe-app@default
   ```

### Option 4 : Serveur SMTP personnalisé
```
MAILER_DSN=smtp://user:password@smtp.example.com:587
```

## Test de l'envoi d'email

1. Créer un rendez-vous en tant que patient
2. Se connecter en tant que médecin ou assistant
3. Confirmer le rendez-vous (changer l'état à "confirmé")
4. Vérifier la réception de l'email dans l'interface de votre service de mail

## Personnalisation

### Modifier l'expéditeur
Dans `src/Service/EmailService.php`, ligne 29 :
```php
->from('noreply@gestionrdv.com')
```

### Modifier le template d'email
Le template se trouve dans `templates/emails/rendez_vous_confirmation.html.twig`

## Dépannage

Si les emails ne sont pas envoyés :
1. Vérifier que le `MAILER_DSN` est correctement configuré dans `.env`
2. Vérifier que le service de mail (Mailhog, etc.) est bien lancé
3. Consulter les logs Symfony : `var/log/dev.log`
4. Tester la configuration mailer :
   ```bash
   php bin/console debug:config framework mailer
   ```
