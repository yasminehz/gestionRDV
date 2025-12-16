Ouvrir l'appli sur le nuage pédagogique https://s5-XXXX.nuage-peda.fr/gestionRDV/public/index.php/register
Avant de push, php bin/console make:migration et php bin/console doctrine:migrations:migrate
Après pull, php bin/console doctrine:migrations:migrate

composer require symfony/mailer

ajouter à la bdd: 
insert into etat (libelle) VALUES ('demandé')
insert into etat (libelle) VALUES ('confirmé');
insert into etat (libelle) VALUES ('annuléé');
