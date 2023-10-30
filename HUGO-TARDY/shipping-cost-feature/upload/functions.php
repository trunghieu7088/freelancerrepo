<?php

// Fonction pour afficher le nombre d'utilisateurs
// 
function afficher_nombre_utilisateurs() {
    $user_counts = count_users("author");
    $total_users = $user_counts['total_users'];
    return 'Le nombre total d\'utilisateurs enregistrés est : ' . $total_users;
}

// Enregistrez le shortcode
add_shortcode('nombre_utilisateurs', 'afficher_nombre_utilisateurs');

// Table of all author users 
// 
function display_authors_in_table($atts) {
    // Récupérer les attributs du shortcode (critères de recherche)
    $atts = shortcode_atts(array(
        'name' => '',
        'email' => ''
    ), $atts);

    // Construire les arguments de recherche pour get_users
    $search_args = array('role' => 'author');
    
    // Ajouter des critères de recherche si des valeurs sont fournies dans le shortcode
    if (!empty($atts['name'])) {
        $search_args['search'] = '*' . sanitize_text_field($atts['name']) . '*';
    }
    
    if (!empty($atts['email'])) {
        $search_args['search_columns'] = array('user_email');
        $search_args['search'] = sanitize_email($atts['email']);
    }

    // Récupérer les utilisateurs correspondant aux critères de recherche
    $authors = get_users($search_args);

    // Vérifier s'il y a des auteurs à afficher
    if (!empty($authors)) {
        // Commencer à construire le tableau HTML
        $output = '<table id="authors-table" style="width: 100%;">';
        $output .= '<thead style="background-color: #8E24AA; color: #fff;">';
        $output .= '<tr>';
        $output .= '<th style="width: 10%; cursor: pointer;" data-sort="avatar">Avatar</th>';
        $output .= '<th style="width: 40%; cursor: pointer;" data-sort="display_name">Nom</th>';
        $output .= '<th style="width: 30%; cursor: pointer;" data-sort="user_registered">Date d\'inscription</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        // Parcourir chaque auteur et ajouter leurs données au tableau
        // ... (Code précédent du shortcode)

// Parcourir chaque auteur et ajouter leurs données au tableau
$row_class = 'even'; // Classe CSS pour les lignes paires
foreach ($authors as $author) {
    $profile_url = home_url('/author/' . $author->user_nicename);
    $avatar = get_avatar($author->ID, 50); // Changer la taille de l'avatar si nécessaire

    $output .= '<tr class="' . $row_class . '">';
    $output .= '<td>' . $avatar . '</td>';
    $output .= '<td><a href="' . esc_url($profile_url) . '">' . esc_html($author->display_name) . '</a></td>';
    $output .= '<td>' . esc_html($author->user_registered) . '</td>';
    $output .= '</tr>';

    // Alternez la classe CSS pour les lignes
    $row_class = ($row_class == 'even') ? 'odd' : 'even';
}

$output .= '</tbody>';
$output .= '</table>';

// JavaScript pour activer le tri des colonnes
$output .= '<script>
    jQuery(document).ready(function($) {
        $("#authors-table").DataTable({
            "order": [],
            "paging": false,
            "info": false,
            "searching": false
        });
    });
</script>';


    } else {
        // Aucun auteur trouvé
        $output = 'Aucun auteur trouvé.';
    }

    return $output;
}

// Enregistrer le shortcode pour l'utiliser dans les articles, les pages, etc.
add_shortcode('display_authors', 'display_authors_in_table');


require('include_custom_function.php');
