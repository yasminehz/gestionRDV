Ouvrir l'appli sur le nuage pédagogique https://s5-XXXX.nuage-peda.fr/gestionRDV/public/index.php/register
Avant de push, php bin/console make:migration et php bin/console doctrine:migrations:migrate
Après pull, php bin/console doctrine:migrations:migrate

composer require symfony/mailer
