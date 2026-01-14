<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("function.php");
include_once("db.php");
include_once("header.php");

// Récupérer toutes les rubriques distinctes
$stmt = $pdo->query("SELECT DISTINCT rubrique FROM articles WHERE rubrique IS NOT NULL AND rubrique != '' ORDER BY rubrique");
$rubriques = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer tous les membres actifs
$stmt = $pdo->query("SELECT id, username, picture_profil, role FROM users WHERE status = 'membre' ORDER BY username");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la recherche
$results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
    $search_performed = true;
    
    $search_text = isset($_POST['search_text']) ? trim($_POST['search_text']) : '';
    $search_category = isset($_POST['search_category']) ? $_POST['search_category'] : 'all';
    $search_author = isset($_POST['search_author']) ? $_POST['search_author'] : '';
    $search_type = isset($_POST['search_type']) ? $_POST['search_type'] : 'articles';
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    $sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : 'recent';
    
    if ($search_type === 'articles') {
        // Recherche d'articles
        $query = "SELECT f.*, u.username, u.picture_profil 
                  FROM articles f 
                  LEFT JOIN users u ON f.author_id = u.id 
                  WHERE 1=1";
        $params = [];
        
        if (!empty($search_text)) {
            $query .= " AND (f.title LIKE :search OR f.content LIKE :search2)";
            $params[':search'] = "%$search_text%";
            $params[':search2'] = "%$search_text%";
        }
        
        if ($search_category !== 'all' && !empty($search_category)) {
            $query .= " AND f.rubrique = :category";
            $params[':category'] = $search_category;
        }
        
        if (!empty($search_author)) {
            $query .= " AND f.author_id = :author";
            $params[':author'] = $search_author;
        }
        
        if (!empty($date_from)) {
            $query .= " AND DATE(f.created_at) >= :date_from";
            $params[':date_from'] = $date_from;
        }
        
        if (!empty($date_to)) {
            $query .= " AND DATE(f.created_at) <= :date_to";
            $params[':date_to'] = $date_to;
        }
        
        // Tri
        switch ($sort_by) {
            case 'oldest':
                $query .= " ORDER BY f.created_at ASC";
                break;
            case 'title':
                $query .= " ORDER BY f.title ASC";
                break;
            case 'author':
                $query .= " ORDER BY u.username ASC";
                break;
            default:
                $query .= " ORDER BY f.created_at DESC";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // Recherche de membres
        $query = "SELECT * FROM users WHERE status = 'membre'";
        $params = [];
        
        if (!empty($search_text)) {
            $query .= " AND (username LIKE :search OR description LIKE :search2)";
            $params[':search'] = "%$search_text%";
            $params[':search2'] = "%$search_text%";
        }
        
        if (!empty($search_author)) {
            $query .= " AND role = :role";
            $params[':role'] = $search_author;
        }
        
        $query .= " ORDER BY username ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée - Aur'art</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .search-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .search-header h1 {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .search-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        
        .search-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .search-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .search-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1em;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .search-tab.active {
            color: #3498db;
            border-bottom-color: #3498db;
            font-weight: bold;
        }
        
        .search-tab:hover {
            color: #3498db;
        }
        
        .search-field-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .search-field {
            display: flex;
            flex-direction: column;
        }
        
        .search-field label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .search-field input,
        .search-field select {
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        .search-field input:focus,
        .search-field select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .search-main-input {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-main-input input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            font-size: 1.1em;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
        }
        
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #ecf0f1;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }
        
        .search-suggestion-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-suggestion-item:hover {
            background: #ecf0f1;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.3em;
        }
        
        .advanced-options {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .advanced-options-toggle {
            background: none;
            border: none;
            color: #3498db;
            cursor: pointer;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .advanced-options-content {
            display: none;
        }
        
        .advanced-options-content.active {
            display: block;
        }
        
        .search-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-search {
            padding: 15px 40px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-reset {
            padding: 15px 40px;
            background: white;
            color: #7f8c8d;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-reset:hover {
            border-color: #7f8c8d;
            color: #2c3e50;
        }
        
        .search-results {
            margin-top: 40px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .results-count {
            font-size: 1.2em;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .result-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .result-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .result-content {
            padding: 20px;
        }
        
        .result-category {
            display: inline-block;
            padding: 5px 12px;
            background: #3498db;
            color: white;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 10px;
        }
        
        .result-title {
            font-size: 1.3em;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .result-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .result-title a:hover {
            color: #3498db;
        }
        
        .result-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .result-author-pic {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .result-excerpt {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .member-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }
        
        .member-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .member-info h3 {
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .member-role {
            color: #3498db;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .no-results-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="search-container">
    <div class="search-header">
        <h1>🔍 Recherche Avancée</h1>
        <p>Trouvez exactement ce que vous cherchez dans notre catalogue</p>
    </div>
    
    <form method="POST" class="search-form" id="searchForm">
        <div class="search-tabs">
            <button type="button" class="search-tab active" data-type="articles">
                📚 Articles
            </button>
            <button type="button" class="search-tab" data-type="members">
                👥 Membres
            </button>
        </div>
        
        <input type="hidden" name="search_type" id="search_type" value="articles">
        
        <div class="search-main-input">
            <input 
                type="text" 
                name="search_text" 
                id="search_text" 
                placeholder="Que recherchez-vous ?"
                autocomplete="off"
                value="<?php echo isset($_POST['search_text']) ? htmlspecialchars($_POST['search_text']) : ''; ?>"
            >
            <span class="search-icon">🔍</span>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>
        
        <div id="articlesFilters">
            <div class="search-field-group">
                <div class="search-field">
                    <label>📁 Catégorie</label>
                    <select name="search_category" id="search_category">
                        <option value="all">Toutes les catégories</option>
                        <?php foreach ($rubriques as $rubrique): ?>
                            <option value="<?php echo htmlspecialchars($rubrique); ?>"
                                <?php echo (isset($_POST['search_category']) && $_POST['search_category'] === $rubrique) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rubrique); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-field">
                    <label>✍️ Auteur</label>
                    <select name="search_author" id="search_author">
                        <option value="">Tous les auteurs</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>"
                                <?php echo (isset($_POST['search_author']) && $_POST['search_author'] == $member['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-field">
                    <label>🔄 Trier par</label>
                    <select name="sort_by" id="sort_by">
                        <option value="recent" <?php echo (isset($_POST['sort_by']) && $_POST['sort_by'] === 'recent') ? 'selected' : ''; ?>>
                            Plus récent
                        </option>
                        <option value="oldest" <?php echo (isset($_POST['sort_by']) && $_POST['sort_by'] === 'oldest') ? 'selected' : ''; ?>>
                            Plus ancien
                        </option>
                        <option value="title" <?php echo (isset($_POST['sort_by']) && $_POST['sort_by'] === 'title') ? 'selected' : ''; ?>>
                            Titre (A-Z)
                        </option>
                        <option value="author" <?php echo (isset($_POST['sort_by']) && $_POST['sort_by'] === 'author') ? 'selected' : ''; ?>>
                            Auteur (A-Z)
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="advanced-options">
                <button type="button" class="advanced-options-toggle" id="advancedToggle">
                    ⚙️ Options avancées ▼
                </button>
                <div class="advanced-options-content" id="advancedContent">
                    <div class="search-field-group">
                        <div class="search-field">
                            <label>📅 Date de début</label>
                            <input 
                                type="date" 
                                name="date_from" 
                                id="date_from"
                                value="<?php echo isset($_POST['date_from']) ? $_POST['date_from'] : ''; ?>"
                            >
                        </div>
                        <div class="search-field">
                            <label>📅 Date de fin</label>
                            <input 
                                type="date" 
                                name="date_to" 
                                id="date_to"
                                value="<?php echo isset($_POST['date_to']) ? $_POST['date_to'] : ''; ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="membersFilters" style="display: none;">
            <div class="search-field-group">
                <div class="search-field">
                    <label>🎭 Rôle</label>
                    <select name="search_author" id="member_role">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Administrateur</option>
                        <option value="utilisateur">utilisateur</option>
                        <option value="membre">membre</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="search-actions">
            <button type="submit" name="search_submit" class="btn-search">
                🔍 Rechercher
            </button>
            <button type="button" class="btn-reset" id="btnReset">
                ↺ Réinitialiser
            </button>
        </div>
    </form>
    
    <?php if ($search_performed): ?>
    <div class="search-results">
        <div class="results-header">
            <div class="results-count">
                <?php echo count($results); ?> résultat<?php echo count($results) > 1 ? 's' : ''; ?> trouvé<?php echo count($results) > 1 ? 's' : ''; ?>
            </div>
        </div>
        
        <?php if (count($results) > 0): ?>
            <div class="results-grid">
                <?php foreach ($results as $item): ?>
                    <?php if (isset($_POST['search_type']) && $_POST['search_type'] === 'members'): ?>
                        <!-- Carte Membre -->
                        <div class="result-card member-card">
                            <?php if (!empty($item['picture_profil'])): ?>
                                <img src="<?php echo htmlspecialchars($item['picture_profil']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['username']); ?>"
                                     class="member-avatar">
                            <?php else: ?>
                                <div class="member-avatar" style="background: #3498db; display: flex; align-items: center; justify-content: center; color: white; font-size: 2em;">
                                    <?php echo strtoupper(substr($item['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="member-info">
                                <h3><?php echo htmlspecialchars($item['username']); ?></h3>
                                <div class="member-role"><?php echo htmlspecialchars($item['role'] ?? 'Membre'); ?></div>
                                <?php if (!empty($item['description'])): ?>
                                    <p><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Carte Article -->
                        <div class="result-card">
                            <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     class="result-image">
                            <?php endif; ?>
                            
                            <div class="result-content">
                                <?php if (!empty($item['rubrique'])): ?>
                                    <span class="result-category">
                                        <?php echo htmlspecialchars($item['rubrique']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <h3 class="result-title">
                                    <a href="article.php?slug=<?php echo urlencode($item['slug']); ?>">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </h3>
                                
                                <div class="result-meta">
                                    <?php if (!empty($item['picture_profil'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['picture_profil']); ?>" 
                                             class="result-author-pic"
                                             alt="<?php echo htmlspecialchars($item['username'] ?? 'Anonyme'); ?>">
                                    <?php endif; ?>
                                    <span>
                                        Par <?php echo htmlspecialchars($item['username'] ?? 'Anonyme'); ?> • 
                                        <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($item['content'])): ?>
                                    <p class="result-excerpt">
                                        <?php echo htmlspecialchars(substr(strip_tags($item['content']), 0, 150)) . '...'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>Aucun résultat trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Gestion des onglets
document.querySelectorAll('.search-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const type = this.dataset.type;
        document.getElementById('search_type').value = type;
        
        if (type === 'articles') {
            document.getElementById('articlesFilters').style.display = 'block';
            document.getElementById('membersFilters').style.display = 'none';
        } else {
            document.getElementById('articlesFilters').style.display = 'none';
            document.getElementById('membersFilters').style.display = 'block';
        }
    });
});

// Options avancées
document.getElementById('advancedToggle').addEventListener('click', function() {
    const content = document.getElementById('advancedContent');
    content.classList.toggle('active');
    this.textContent = content.classList.contains('active') 
        ? '⚙️ Options avancées ▲' 
        : '⚙️ Options avancées ▼';
});

// Réinitialiser le formulaire
document.getElementById('btnReset').addEventListener('click', function() {
    document.getElementById('searchForm').reset();
    window.location.href = window.location.pathname;
});

// Suggestions de recherche (auto-complétion)
const searchInput = document.getElementById('search_text');
const suggestionsDiv = document.getElementById('searchSuggestions');

searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    if (query.length < 2) {
        suggestionsDiv.style.display = 'none';
        return;
    }
    
    // Simulation de suggestions (à remplacer par un appel AJAX)
    const suggestions = ['Art moderne', 'Peinture', 'Sculpture', 'Photographie', 'Art contemporain'];
    const filtered = suggestions.filter(s => s.toLowerCase().includes(query.toLowerCase()));
    
    if (filtered.length > 0) {
        suggestionsDiv.innerHTML = filtered.map(s => 
            `<div class="search-suggestion-item">${s}</div>`
        ).join('');
        suggestionsDiv.style.display = 'block';
        
        document.querySelectorAll('.search-suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.textContent;
                suggestionsDiv.style.display = 'none';
            });
        });
    } else {
        suggestionsDiv.style.display = 'none';
    }
});

// Fermer les suggestions en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        suggestionsDiv.style.display = 'none';
    }
});
</script>

</body>
</html>