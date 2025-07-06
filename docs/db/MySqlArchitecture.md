### 1. **cache**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| key          | string       | PK       |                   |
| value        | mediumText   |          |                   |
| expiration   | integer      |          |                   |

### 2. **cache_locks**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| key          | string       | PK       |                   |
| owner        | string       |          |                   |
| expiration   | integer      |          |                   |

### 3. **tags**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       | Unique   |                   |
| slug         | string       | Unique   |                   |

### 4. **quiz_levels**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       |          |                   |

### 5. **roles**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       | Unique   |                   |
| description  | string       |          | Nullable          |

### 6. **organizations**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       | Unique   |                   |

### 7. **teams**
| Champ            | Type         | Clé      | Remarques         |
|------------------|--------------|----------|-------------------|
| id               | bigint       | PK       | AI                |
| organization_id  | bigint       | FK       | organizations.id  |
| name             | string       |          |                   |

### 8. **question_types**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       | Unique   |                   |

### 9. **users**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| username     | string       | Unique   |                   |
| email        | string       | Unique   |                   |
| password     | string       |          |                   |
| firstname    | string       |          | Nullable          |
| lastname     | string       |          | Nullable          |
| role_id      | bigint       | FK       | roles.id, Nullable|
| avatar       | string       |          | Nullable          |
| ranking      | integer      | Unique   | Nullable          |
| team_id      | bigint       | FK       | teams.id, Nullable|

### 10. **quizzes**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| title        | string(255)  |          |                   |
| slug         | string(255)  | Unique   |                   |
| description  | text         |          | Nullable          |
| is_public    | boolean      |          | Default: false    |
| level_id     | bigint       | FK       | quiz_levels.id    |
| status       | enum         |          | draft/published/archived |
| user_id      | bigint       | FK       | users.id          |
| duration     | integer      |          | Nullable          |
| pass_score   | integer      |          | Nullable          |
| thumbnail    | string(255)  |          | Nullable          |
| category_id  | bigint       | FK       | categories.id, Nullable |

### 11. **questions**
| Champ            | Type         | Clé      | Remarques         |
|------------------|--------------|----------|-------------------|
| id               | bigint       | PK       | AI                |
| quiz_id          | bigint       | FK       | quizzes.id        |
| content          | text         |          |                   |
| question_type_id | bigint       | FK       | question_types.id |

### 12. **answers**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| question_id  | bigint       | FK       | questions.id      |
| content      | string       |          |                   |
| is_correct   | boolean      |          | Default: false    |

### 13. **badges**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       |          |                   |
| description  | text         |          | Nullable          |
| icon         | string       |          |                   |

### 14. **user_badges**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| user_id      | bigint       | FK       | users.id          |
| badge_id     | bigint       | FK       | badges.id         |

### 15. **scores**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| user_id      | bigint       | FK       | users.id          |
| quiz_id      | bigint       | FK       | quizzes.id        |
| score        | integer      |          |                   |

### 16. **quiz_schedules**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| quiz_id      | bigint       | FK       | quizzes.id        |
| user_id      | bigint       | FK       | users.id          |
| start_time   | dateTime     |          |                   |
| end_time     | dateTime     |          |                   |

### 17. **exports_imports**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| type         | string       |          |                   |
| entity       | string       |          |                   |
| file_path    | string       |          |                   |
| user_id      | bigint       | FK       | users.id          |

### 18. **personal_access_tokens**
| Champ          | Type         | Clé      | Remarques         |
|----------------|--------------|----------|-------------------|
| id             | bigint       | PK       | AI                |
| tokenable_id   | bigint       |          |                   |
| tokenable_type | string       |          |                   |
| name           | string       |          |                   |
| token          | string(64)   | Unique   |                   |
| abilities      | text         |          | Nullable          |
| last_used_at   | timestamp    |          | Nullable          |
| expires_at     | timestamp    |          | Nullable          |

### 19. **quiz_user**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| quiz_id      | bigint       | FK       | quizzes.id        |
| user_id      | bigint       | FK       | users.id          |
| guest_uuid   | uuid         |          | Nullable          |

### 20. **quiz_tag**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| quiz_id      | bigint       | FK       | quizzes.id        |
| tag_id       | bigint       | FK       | tags.id           |

### 21. **categories**
| Champ        | Type         | Clé      | Remarques         |
|--------------|--------------|----------|-------------------|
| id           | bigint       | PK       | AI                |
| name         | string       | Unique   |                   |
| created_at   | timestamp    |          |                   |
