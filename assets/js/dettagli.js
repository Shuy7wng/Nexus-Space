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
    fetch(`/Nexus-Space/actions/comments_handler.php?id_opera=${idOpera}`)
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
            // map trasforma ogni commento in una stringa HTML, 
            // poi join unisce tutte le stringhe in un'unica stringa da inserire nel modal
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
document.querySelectorAll('.btn-comment').forEach(button => { // Seleziono tutti i pulsanti "Commenta"

    button.addEventListener('click', function() { // Aggiungo un evento click a ciascun pulsante

        // Prende l'id dell'opera dal data attribute del pulsante cliccato
        // Dataset è un oggetto che contiene tutti gli attributi data-* dell'elemento HTML, in questo caso data-id
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

        // Di default il form invia una richiesta HTTP e ricarica la pagina, 
        // quindi prevengo il comportamento di default
        e.preventDefault(); 

        // Se per qualche motivo non ho un'opera selezionata, esco
        if(!currentOperaId) return;

        // Leggo il testo del commento dall'input del form, rimuovendo eventuali spazi all'inizio e alla fine
        const commentInput = commentForm.querySelector('input[name="commento"]');
        const commentText = commentInput.value.trim();

        // Se il commento è vuoto, non invio la richiesta al server
        if(!commentText) return;

        fetch('/Nexus-Space/actions/comments_handler.php', {
            method: 'POST', // POST perché sto inviando dati
            headers: { 'Content-Type': 'application/json'}, // Indico che sto inviando dati in formato JSON
            // Stringify converte l'oggetto JavaScript in una stringa JSON da inviare al server
            body: JSON.stringify({
                id_opera: currentOperaId,
                commento: commentText
            })
        })
        .then(response => {

            if (!response.ok) {
                throw new Error("Errore HTTP");
            }

            return response.json();
        })
        .then(response => {

            // Se il server dice che non sono loggata → redirect alla pagina di login
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
            headers: { 
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_opera: idOpera
            })
        })
        .then(response => {

            if (!response.ok) {
                throw new Error("Errore HTTP");
            }

            return response.json(); // Qui il server restituisce testo semplice
        })
        .then(response => {

            if (response.status === 'not_logged') {
                window.location.href = '/Nexus-Space/pages/login.php';
                return;
            }

            if (response.status === 'added') {

                button.innerText = '♥';
                button.classList.add('liked');
                countElem.textContent = count + 1;
            }

            if (response.status === 'removed') {

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