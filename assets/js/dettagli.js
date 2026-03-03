// --- Riferimenti agli elementi DOM ---
const modal = document.getElementById('comment-modal');
const closeBtn = document.getElementById('close-modal');
const modalBody = document.getElementById('modal-comment-list');
const commentForm = document.getElementById('modal-comment-form');

let currentOperaId = null; // Tiene traccia dell'opera selezionata


// --- FUNZIONE PER CARICARE I COMMENTI ---
function loadComments(idOpera) {

    // Mostro un messaggio temporaneo mentre il server risponde
    modalBody.innerHTML = '<p class="loading">Caricamento commenti...</p>';

    // Effettuo una richiesta GET al server per ottenere i commenti dell'opera selezionata
    fetch(`/Nexus-Space/actions/comments_handler.php?id_opera=${idOpera}`, {
        credentials: 'include' // IMPORTANTE: invia i cookie per mantenere la sessione PHP
    })
    .then(response => {

        // Controllo se la risposta HTTP è valida (status 200-299)
        if (!response.ok) {
            throw new Error("Errore HTTP");
        }

        return response.json(); // Converto la risposta in oggetto JavaScript
    })
    .then(comments => {

        if (comments.length === 0) {

            modalBody.innerHTML = '<p class="inter">Nessun commento presente.</p>';

        } else {

            // Inserisco i commenti nel modal, evidenziando il nickname in grassetto
            modalBody.innerHTML = comments.map(c =>
                `<div class="comment"><strong>${c.nickname}</strong>: ${c.commento}</div>`
            ).join(''); // Unisce le stringhe: nomeUtente + commento
        }

    })
    .catch(error => {

        console.error("Errore caricamento commenti:", error);
        modalBody.innerHTML = '<p class="inter">Errore nel caricamento dei commenti.</p>';
    });
}


// --- APERTURA MODAL COMMENTI ---
document.querySelectorAll('.btn-comment').forEach(button => {

    button.addEventListener('click', function() {

        // Recupero ID dal data-id (dataset legge attributi data-*)
        currentOperaId = this.dataset.id;

        modal.style.display = 'flex';

        loadComments(currentOperaId);
    });
});


// --- CHIUSURA MODAL ---
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

window.addEventListener('click', function(e) {

    // Chiudo il modal solo se clicco sullo sfondo
    if(e.target === modal){
        modal.style.display = 'none';
    }
});


// --- GESTIONE INVIO COMMENTI ---
if(commentForm){

    commentForm.addEventListener('submit', function(e){

        e.preventDefault(); // Blocca il refresh della pagina

        // Se per qualche motivo non ho un'opera selezionata, esco
        if(!currentOperaId) return;

        const commentInput = commentForm.querySelector('input[name="commento"]');
        const commentText = commentInput.value.trim();

        if(!commentText) return;

        fetch('/Nexus-Space/actions/comments_handler.php', {
            method: 'POST', // POST perché sto inviando dati
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'include', // Serve per mantenere la sessione utente
            body:
                'id_opera=' + encodeURIComponent(currentOperaId) +
                '&commento=' + encodeURIComponent(commentText)
                // encodeURIComponent evita problemi con spazi e caratteri speciali
        })
        .then(response => {

            if (!response.ok) {
                throw new Error("Errore HTTP");
            }

            return response.json();
        })
        .then(response => {

            // Se il server dice che non sono loggata → redirect
            if (response.status === 'not_logged') {
                window.location.href = '/Nexus-Space/pages/login.php';
                return;
            }

            commentInput.value = '';
            loadComments(currentOperaId); // Ricarico i commenti senza refresh

        })
        .catch(error => {
            console.error("Errore invio commento:", error);
        });
    });

}


// --- GESTIONE LIKE ---
document.querySelectorAll('.btn-like').forEach(button => {

    button.addEventListener('click', function() {

        const idOpera = this.dataset.id;

        const countElem = document.getElementById(`like-count-${idOpera}`);

        // Leggo il numero di like attuale, convertendolo in intero (base 10)
        let count = parseInt(countElem.textContent, 10);

        fetch('/Nexus-Space/actions/add_like.php', {
            method: 'POST',
            headers: { // Indico che sto inviando dati in formato URL-encoded
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'include',
            body: 'id_opera=' + encodeURIComponent(idOpera)
        })
        .then(response => {

            if (!response.ok) {
                throw new Error("Errore HTTP");
            }

            return response.text(); // Qui il server restituisce testo semplice
        })
        .then(response => {

            if (response === 'not_logged') {
                window.location.href = '/Nexus-Space/pages/login.php';
                return;
            }

            if (response === 'added') {

                button.innerText = '♥';
                button.classList.add('liked');
                countElem.textContent = count + 1;
            }

            if (response === 'removed') {

                button.innerText = '♡';
                button.classList.remove('liked');
                countElem.textContent = count - 1;
            }

        })
        .catch(error => {
            console.error("Errore richiesta like:", error);
        });
    });
});