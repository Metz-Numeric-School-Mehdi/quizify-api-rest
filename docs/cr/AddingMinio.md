# Intégration de MinIO pour la gestion des photos de profil sur Quizify

## Pourquoi ce choix ?

Dans le cadre de Quizify, il était nécessaire de permettre aux utilisateurs d’ajouter une photo de profil. Plutôt que de stocker ces fichiers sur le serveur local ou dans la base de données, nous avons choisi MinIO, une solution de stockage objet compatible S3, pour les raisons suivantes :

- **Scalabilité** : MinIO permet de gérer un grand nombre de fichiers sans impacter les performances de l’application.
- **Sécurité** : Les fichiers peuvent rester privés et accessibles uniquement via des URLs temporaires générées côté backend.
- **Interopérabilité** : Compatible avec l’écosystème AWS S3, facilitant une éventuelle migration ou extension.
- **Simplicité de déploiement** : Facile à intégrer en local via Docker, et déployable en production sur n’importe quel serveur.

---

## Étapes de l’intégration de MinIO

Le processus d’intégration s’est déroulé en plusieurs étapes, détaillées ci-dessous.

## 1. Installation et configuration de MinIO

- Ajout d’un service MinIO dans `docker/docker-compose.yml` pour faciliter le développement local.
  - Ports exposés : 9000 (API), 9001 (console web)
  - Identifiants par défaut : minioadmin / minioadmin
- Lancement du container MinIO avec Docker Compose.
- Accès à la console d’administration via `http://localhost:9001` pour créer le bucket `profile-pictures` qui contiendra toutes les photos de profil.

---

## 2. Intégration à Laravel

- Ajout d’un disque `minio` dans `config/filesystems.php` utilisant le driver S3 et pointant vers l’instance MinIO locale.
- Pourquoi modifier `config/filesystems.php` ?  
  Laravel centralise la gestion de tous les systèmes de fichiers (local, S3, FTP, etc.) dans ce fichier.  
  Ajouter MinIO ici permet d’utiliser la même API Laravel (`Storage::disk('minio')`) pour stocker et récupérer les fichiers, tout en gardant la flexibilité de changer de solution de stockage sans modifier le code métier.
- Ajout des variables d’environnement suivantes dans `.env` :

```
MINIO_ENDPOINT=http://localhost:9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_BUCKET=profile-pictures
FILESYSTEM_DISK=minio
```

- Modification de la migration de la table `users` pour ajouter la colonne `profile_photo` qui stockera le nom du fichier de la photo de profil.

---

## 3. Modification du processus d’inscription

- Adaptation de la méthode `signUp` dans `AuthController` pour accepter un champ `photo` (image) lors de l’inscription.
- Si une photo est envoyée, elle est uploadée dans MinIO via le disque Laravel configuré.
- Le nom du fichier est stocké dans la colonne `profile_photo` de l’utilisateur.
- Une URL temporaire (pré-signée) est générée et renvoyée dans la réponse API sous le champ `profile_photo_url` pour permettre l’affichage immédiat de la photo côté front.

---

## 4. Affichage de la photo côté front

- Après inscription, le front récupère le champ `profile_photo_url` dans la réponse de l’API.
- Cette URL peut être utilisée directement comme source d’une balise `<img>`, permettant d’afficher la photo de profil immédiatement.
- L’URL est temporaire (valable 60 minutes), ce qui garantit la sécurité et la confidentialité des fichiers utilisateurs.

---

## 5. Sécurité et bonnes pratiques

- Les photos ne sont jamais publiques : seules les personnes authentifiées peuvent obtenir une URL temporaire via l’API.
- Le bucket MinIO reste privé, ce qui protège les données utilisateurs.
- L’utilisation d’URL temporaires évite la fuite ou l’indexation des fichiers sensibles.

---

## 6. Points techniques clés

- Ajout de la colonne `profile_photo` à la table `users` via une migration.
- Utilisation de la méthode Laravel `Storage::disk('minio')->temporaryUrl()` pour générer les liens d’accès sécurisés.
- Stockage du nom du fichier uniquement en base, jamais l’URL complète.

---

## 7. Procédure de test

- Effectuer une inscription via `/api/auth/signup` en envoyant un champ `photo` (type fichier).
- Vérifier la présence du fichier dans le bucket MinIO.
- Vérifier que la réponse API contient bien le champ `profile_photo_url`.
- Afficher la photo sur le front à l’aide de cette URL.

---