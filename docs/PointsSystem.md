# 🎯 Système de Points Quizify

## Vue d'ensemble

Le système de points de Quizify récompense les utilisateurs selon leur performance aux quiz, avec des bonus basés sur la difficulté, la précision et la vitesse.

## 📊 Calcul des Points

### 🔢 Points de Base
- **10 points** par réponse correcte
- Multiplié par le nombre de bonnes réponses

### 🏆 Multiplicateurs de Niveau
| Niveau | Multiplicateur | Description |
|--------|----------------|-------------|
| 1 | x1.0 | Facile |
| 2 | x1.5 | Moyen |
| 3 | x2.0 | Difficile |
| 4 | x3.0 | Expert |

### 🎖️ Bonus de Performance
Basé sur le pourcentage de réussite :
- **100%** : +50 points (Parfait !)
- **90%+** : +30 points (Excellent)
- **80%+** : +20 points (Très bien)
- **70%+** : +10 points (Bien)

### ⚡ Bonus de Vitesse
- **+25 points maximum** si le quiz est terminé en moins de 50% du temps alloué
- Bonus proportionnel à la vitesse

## 🧮 Exemple de Calcul

**Quiz niveau Moyen (x1.5) - 5 questions - 30 minutes**
- Réponses correctes : 5/5
- Temps passé : 10 minutes

**Calcul :**
1. Points de base : 5 × 10 = 50 points
2. Multiplicateur niveau : 50 × 1.5 = 75 points
3. Bonus performance (100%) : +50 points
4. Bonus vitesse (10min sur 30min) : +20 points
5. **Total : 145 points**

## 🔧 Configuration Technique

### Service `PointsCalculationService`

```php
// Utilisation
$pointsService = new PointsCalculationService();
$pointsData = $pointsService->calculatePoints($quiz, $correctAnswers, $totalQuestions, $timeSpent);
```

### Configuration des Points

```php
const POINTS_CONFIG = [
    'base_points' => 10,
    'level_multipliers' => [
        1 => 1.0,  // Facile
        2 => 1.5,  // Moyen
        3 => 2.0,  // Difficile
        4 => 3.0,  // Expert
    ],
    'bonus_thresholds' => [
        100 => 50,  // Parfait
        90  => 30,  // Excellent
        80  => 20,  // Très bien
        70  => 10,  // Bien
    ],
    'time_bonus' => [
        'enabled' => true,
        'max_bonus' => 25,
        'threshold_percent' => 50,
    ]
];
```

## 📡 API Endpoints

### Obtenir les points de l'utilisateur
```http
GET /api/points/user
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "user_id": 1,
  "username": "johndoe",
  "total_points": 1250
}
```

### Points par catégorie
```http
GET /api/points/user/category/{categoryId}
Authorization: Bearer {token}
```

### Classement des points
```http
GET /api/points/leaderboard?limit=10
```

**Réponse :**
```json
{
  "leaderboard": [
    {
      "id": 1,
      "username": "johndoe",
      "total_points": 1250,
      "quiz_attempts_count": 15
    }
  ],
  "limit": 10,
  "total_users": 1
}
```

### Configuration du système
```http
GET /api/points/config
```

## 💾 Structure de Données

### Soumission de Quiz
```json
POST /api/quizzes/{id}/submit
{
  "responses": [
    {
      "question_id": 1,
      "answer_id": 3
    }
  ],
  "time_spent": 600
}
```

### Réponse Enrichie
```json
{
  "score": 5,
  "total": 5,
  "percentage": 100.0,
  "points": {
    "base_points": 50,
    "level_multiplier": 1.5,
    "level_points": 75,
    "performance_bonus": 50,
    "speed_bonus": 20,
    "total_points": 145,
    "breakdown": {
      "correct_answers": 5,
      "total_questions": 5,
      "success_rate": 100,
      "quiz_level": 2,
      "time_spent": 600,
      "quiz_duration": 30
    }
  },
  "quiz_attempt_id": 123
}
```

## 🗃️ Tables de Base de Données

### Table `scores`
```sql
- id: Primary Key
- user_id: Foreign Key -> users.id
- quiz_id: Foreign Key -> quizzes.id
- score: Points attribués (int)
- created_at, updated_at, deleted_at
```

### Table `quiz_attempts`
```sql
- id: Primary Key
- user_id: Foreign Key -> users.id
- quiz_id: Foreign Key -> quizzes.id
- score: Nombre de bonnes réponses (int)
- max_score: Nombre total de questions (int)
- created_at, updated_at
```

### Table `question_responses`
```sql
- user_id: Foreign Key -> users.id (ajouté)
- quiz_id, question_id, answer_id
- user_answer: Réponse textuelle libre
- is_correct: Boolean
```

## 🎮 Gamification

### Mécaniques Implémentées
- ✅ **Points progressifs** selon difficulté
- ✅ **Bonus de performance** pour excellence
- ✅ **Bonus de vitesse** pour rapidité
- ✅ **Classement global** et par catégorie
- ✅ **Historique des tentatives**

### Évolutions Possibles
- 🔄 **Multiplicateurs temporels** (événements spéciaux)
- 🔄 **Points d'expérience** et niveaux utilisateur
- 🔄 **Défis quotidiens/hebdomadaires**
- 🔄 **Bonus de série** (streak)
- 🔄 **Compétitions entre équipes**

## ⚠️ Points d'Attention

### Performance
- Calculs en mémoire (pas de requêtes lourdes)
- Transactions pour consistance des données
- Index sur `user_id` et `quiz_id`

### Équité
- Temps limité pour éviter la triche
- Validation côté serveur uniquement
- Logs détaillés pour audit

### Extensibilité
- Configuration centralisée dans le service
- Interface pour nouveaux types de bonus
- Support multi-catégories

## 🧪 Tests

```bash
# Exécuter les tests du système de points
php artisan test tests/Feature/PointsSystemTest.php
```

Le fichier de test couvre :
- Calcul correct des points selon les critères
- Attribution et persistance en base
- Endpoints API et authentification
- Cas limites et erreurs
