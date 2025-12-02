# Jeu d'essai (Fixtures) - Gestion des Rendez-vous

## Description

Ce document décrit les données de test (fixtures) créées pour l'application de gestion des rendez-vous médicaux.

### Mot de passe par défaut

Tous les comptes de test utilisent le mot de passe : **`password123`**

---

## Médecins (3)

| Email | Nom | Prénom | Rôle |
|-------|-----|--------|------|
| dr.martin@clinic.fr | Martin | Jean | ROLE_MEDECIN |
| dr.dupont@clinic.fr | Dupont | Marie | ROLE_MEDECIN |
| dr.bernard@clinic.fr | Bernard | Pierre | ROLE_MEDECIN |

---

## Assistants (4)

| Email | Nom | Prénom | Médecin | Rôle |
|-------|-----|--------|---------|------|
| assistant.sophie@clinic.fr | Moreau | Sophie | Dr. Jean Martin | ROLE_ASSISTANT |
| assistant.luc@clinic.fr | Lefevre | Luc | Dr. Jean Martin | ROLE_ASSISTANT |
| assistant.isabelle@clinic.fr | Richard | Isabelle | Dr. Marie Dupont | ROLE_ASSISTANT |
| assistant.thomas@clinic.fr | Petit | Thomas | Dr. Pierre Bernard | ROLE_ASSISTANT |

---

## Patients (6)

| Email | Nom | Prénom | Rôle |
|-------|-----|--------|------|
| jean.patient@mail.fr | Patient | Jean | ROLE_PATIENT |
| marie.patient@mail.fr | Dupuis | Marie | ROLE_PATIENT |
| marc.patient@mail.fr | Leclerc | Marc | ROLE_PATIENT |
| anne.patient@mail.fr | Moreau | Anne | ROLE_PATIENT |
| paul.patient@mail.fr | Fournier | Paul | ROLE_PATIENT |
| claire.patient@mail.fr | Girard | Claire | ROLE_PATIENT |

---

## Demandes de rendez-vous (9)

### Patient Jean Patient avec Dr. Jean Martin
1. **Demande 1** - Demain (+1 jour) - État : **DEMANDE**
2. **Demande 2** - +3 jours - État : **CONFIRME**

### Patient Marie Dupuis avec Dr. Jean Martin
3. **Demande 3** - +2 jours - État : **DEMANDE**

### Patient Marc Leclerc avec Dr. Marie Dupont
4. **Demande 4** - +5 jours - État : **CONFIRME**
5. **Demande 5** - +7 jours - État : **DONE** (rendez-vous effectué)

### Patient Anne Moreau avec Dr. Pierre Bernard
6. **Demande 6** - +4 jours - État : **REFUSE**

### Patient Paul Fournier avec Dr. Jean Martin
7. **Demande 7** - +6 jours - État : **CONFIRME**

### Patient Claire Girard avec Dr. Marie Dupont
8. **Demande 8** - +2 jours - État : **DEMANDE**
9. **Demande 9** - +8 jours - État : **ANNULE**

---

## États des demandes

| État | Description |
|------|------------|
| **DEMANDE** | Demande initialement soumise, en attente de confirmation |
| **CONFIRME** | Rendez-vous confirmé par le médecin |
| **DONE** | Rendez-vous effectué |
| **REFUSE** | Rendez-vous refusé par le médecin |
| **ANNULE** | Rendez-vous annulé |

---

## Charger les fixtures

Pour charger (ou recharger) les fixtures dans la base de données :

```bash
php bin/console doctrine:fixtures:load
```

Pour recharger les fixtures sans confirmation :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Notes

- Les fixtures sont définies dans `src/DataFixtures/AppFixtures.php`
- La classe utilise le `PasswordHasher` de Symfony pour sécuriser les mots de passe
- Lors du chargement des fixtures, la base de données est automatiquement purgée
- Les relations entre les entités sont correctement établies (les assistants sont associés aux médecins, etc.)
