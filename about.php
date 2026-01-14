  <link rel="stylesheet" href="style.css">
<?php
// presentation.php
session_start();
include('header.php');
include('function.php');
include('db.php');


?>

<h2>Présentation de l'association</h2>

<section class="asso-intro">
    <p>
        Notre association a pour mission de célébrer l’histoire de l’art, de mettre en lumière le patrimoine artistique 
        et de favoriser la transmission des savoirs. Nous croyons que l’art est un langage universel qui relie les générations, 
        inspire la réflexion et enrichit notre compréhension du monde.
    </p>

    <p>
        Nous organisons régulièrement des rencontres, conférences, expositions, ateliers et initiatives culturelles afin de 
        partager notre passion avec un public varié. Notre objectif : contribuer à une culture artistique vivante, ouverte et accessible à tous.
    </p>
</section>



<h2>Notre équipe</h2>


<div class="profile-sidebar"> 
        <?php 
        $total_products = count_users($pdo);
        $status_membre="membre";
        
        
        for ($index = 1; $index <= $total_products; $index++):
            $all_users = return_info_users($index);

            
        ?>
       
       
 <?php if(!empty($all_users['status']) && ($all_users['status']===$status_membre)): ?>
        <div class="profile-card">
            <div class="profile-picture-container"> 
               
                
                     <?php if (!empty($all_users['picture_profil']) && file_exists($all_users['picture_profil'])) :  ?>
                    <img src="<?= htmlspecialchars($all_users['picture_profil']) ?>" alt="Photo de profil" class="profile-picture">
                <?php else: ?>
                
                    <div class="profile-placeholder">
                        <?= strtoupper(substr($all_users['username'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                </div>
            <h2><?= htmlspecialchars($all_users['username'] ?? 'Utilisateur') ?></h2>
            
            <?php if (!empty($all_users['role'])): ?>
                <div class="role"><?= htmlspecialchars($all_users['role']) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($all_users['description'])): ?>
                <div class="description"><?= htmlspecialchars($all_users['description']) ?></div>
            <?php endif; ?>
            
            <div class="profile-stats">
                <div class="stat">
                    <span class="stat-number">
                        <?php
                        $count = q("SELECT COUNT(*) as total FROM articles WHERE author_id = ?", 'i', [$all_users['id']]);
                        echo $count ? $count->fetch_assoc()['total'] : 0;
                        ?>
                    </span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= date('Y') - (int)date('Y', strtotime($all_users['created_at'] ?? 'now')) ?></span>
                    <span class="stat-label">Années</span>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        <?php 
        endfor;
        ?>
</div>


<style>

  .container_dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
    }

    /* Sidebar profil */
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .profile-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        text-align: center;
    }

    .profile-picture-container {
        margin-bottom: 20px;
    }

    .profile-picture {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #667eea;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .profile-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        color: white;
        font-weight: bold;
        margin: 0 auto;
    }

    .profile-card h2 {
        color: #333;
        margin: 15px 0 5px;
        font-size: 24px;
    }

    .profile-card .role {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 15px;
    }

   .profile-card .description {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-top: 15px;
   

    white-space: normal;   /* ⬅ Force le retour à la ligne automatique */
}

    .profile-stats {
        display: flex;
        justify-content: space-around;
        padding: 20px 0;
        border-top: 2px solid #eee;
        margin-top: 20px;
    }

    .stat {
        text-align: center;
    }

    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        margin-top: 5px;
    }





</style>

<style>
/* --- STYLE --- */
/* Tu peux évidemment déplacer ces styles dans ton fichier CSS */
.team-section {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin: 20px 0;
}

.member-card {
    width: 350px;
    padding: 15px;
    background: #fafafa;
    border-radius: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

.member-photo {
    width: 100%;
    height: auto;
    border-radius: 8px;
    margin-bottom: 10px;
}

.role {
    font-weight: bold;
    color: #444;
}

.desc {
    font-size: 0.9em;
    color: #555;
}

/* Section contact */
.contact-section {
    margin-top: 40px;
    padding: 20px;
    background: #f2f2f2;
    border-radius: 10px;
}

.btn-contact {
    display: inline-block;
    padding: 10px 20px;
    background: #333;
    color: white;
    border-radius: 5px;
    text-decoration: none;
}

.btn-contact:hover {
    background: #555;
}
</style>



<h2>Contactez-nous</h2>

<section class="contact-section">
    <p>Vous pouvez nous contacter pour toute question ou projet :</p>

    <ul>
        <li><strong>Email :</strong></li>
        <li><strong>Téléphone :</strong>   </li>
        <li><strong>Adresse :</strong></li>
    </ul>

    <p>Ou via notre formulaire de contact :</p>

    <a href="contact.php" class="btn-contact">Accéder au formulaire</a>
</section>

<?php
include('footer.php');
?>


