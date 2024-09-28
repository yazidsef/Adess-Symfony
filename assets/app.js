import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './styles/main.css';

document.querySelectorAll('.amount').forEach(function(label) {
    label.addEventListener('click', function() {
        // Retirer le style sélectionné de tous les montants
        document.querySelectorAll('.amount').forEach(function(l) {
            l.style.backgroundColor = '#f5f5f5';
            l.style.color = '#333';
        });

        // Appliquer le style sélectionné
        label.style.backgroundColor = '#f28b30';
        label.style.color = '#fff';

        // Coche le bouton radio correspondant
        label.querySelector('input[type="radio"]').checked = true;
    });
});
