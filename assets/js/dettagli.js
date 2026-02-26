// --- Riferimenti agli elementi DOM ---
const modal = document.getElementById('comment-modal'); // Il div del modal commenti
const closeBtn = document.getElementById('close-modal'); // Pulsante X per chiudere il modal
const modalBody = document.getElementById('modal-comment-list'); // Div dove inserire i commenti
const commentForm = document.getElementById('modal-comment-form'); // Form per aggiungere commenti

let currentOperaId = null; // Tiene traccia dell'opera corrente selezionata

// --- FUNZIONE PER CARICARE I COMMENTI VIA AJAX ---
function loadComments(idOpera) {
    // Mostra messaggio di caricamento
    modalBody.innerHTML = '<p class="loading">Caricamento commenti...</p>';

    // 1. Creazione dell'oggetto XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // 2. Apertura della richiesta (GET) al file PHP con l'ID dell'opera come parametro
    xhr.open('GET', `/Nexus-Space/actions/comments_handler.php?id_opera=${idOpera}`, true);

    // 3. Impostazione della funzione di callback (ogni volta che lo stato della richiesta AJAX cambia, viene eseguita) 
    // per gestire la risposta
    xhr.onreadystatechange = function() {
        if(xhr.readyState === XMLHttpRequest.DONE) { // Verifico se la richiesta è completata (readyState 4)
            if(xhr.status === 200) { // Verifico se la risposta del server è OK (status 200)
                try {
                    // 4. Parsing della risposta JSON (che dovrebbe essere un array di commenti)
                    const comments = JSON.parse(xhr.responseText); 

                    // Se non ci sono commenti, mostro un messaggio
                    if(comments.length === 0){
                        modalBody.innerHTML = '<p class="inter">Nessun commento presente.</p>';
                    } else {
                        
                        // Altrimenti, costruisco l'HTML per visualizzare i commenti
                        // Map trasforma ogni elemento dell'array in una stringa HTML, 
                        modalBody.innerHTML = comments.map(c => 
                            `<div class="comment"><strong>${c.nickname}</strong>: ${c.commento}</div>`
                        ).join(''); // poi join unisce tutte le stringhe in un'unica stringa da inserire nel modal
                    }
                } catch(e) {
                    console.error('Errore parsing JSON commenti:', e);
                    modalBody.innerHTML = '<p class="inter">Errore nel caricamento dei commenti.</p>';
                }
            } else {
                console.error('Errore richiesta commenti:', xhr.status);
                modalBody.innerHTML = '<p class="inter">Errore nel caricamento dei commenti.</p>';
            }
        }
    };

    xhr.send();
}

// --- APERTURA MODAL COMMENTI ---
// Seleziono tutti i bottoni con classe "btn-comment"
document.querySelectorAll('.btn-comment').forEach(button => {

    // Aggiungo un event listener per il click su ciascun bottone
    button.addEventListener('click', function() {

        // 1. Recupero l'ID dell'opera dal data-id del bottone cliccato
        // dataset permette di leggere attributi HTML del tipo data-*
        currentOperaId = this.dataset.id; 

        // 2. Mostro il modal impostando il display a flex
        modal.style.display = 'flex';    

        // 3. Carico i commenti relativi a quell'opera tramite AJAX
        // Chiamo la funzione loadComments passando l'ID selezionato
        loadComments(currentOperaId);     
    });
});

// --- CHIUSURA MODAL ---
// Quando clicco sul pulsante X
closeBtn.addEventListener('click', function() {

    // Nascondo il modal impostando display: none
    modal.style.display = 'none';
});


// Quando clicco in qualsiasi punto della finestra
window.addEventListener('click', function(e) {

    // Se l'elemento cliccato è proprio lo sfondo del modal (non il contenuto interno)
    if(e.target === modal){

        // Chiudo il modal
        modal.style.display = 'none';
    }
});

// --- GESTIONE INVIO COMMENTI ---
// Verifico che il form esista prima di aggiungere l'event listener
// (evita errori se il form non è presente in alcune pagine)
if(commentForm){

    // Intercetto l'evento submit del form
    commentForm.addEventListener('submit', function(e){

        // 1. Impedisco il comportamento predefinito del form
        // (che sarebbe ricaricare la pagina)
        e.preventDefault();

        // 2. Se non è selezionata nessuna opera, interrompo l'esecuzione
        if(!currentOperaId) return;

        // 3. Recupero il campo input del commento
        const commentInput = commentForm.querySelector('input[name="commento"]');

        // 4. Prendo il valore inserito e rimuovo eventuali spazi iniziali/finali
        const commentText = commentInput.value.trim();

        // 5. Se il commento è vuoto, non faccio nulla
        if(!commentText) return;

        // --- CREAZIONE RICHIESTA AJAX POST ---

        // 6. Creo un nuovo oggetto XMLHttpRequest
        const xhr = new XMLHttpRequest();

        // 7. Apro la richiesta POST verso il file PHP che gestisce i commenti
        xhr.open('POST', '/Nexus-Space/actions/comments_handler.php', true);

        // 8. Imposto l'header per indicare che sto inviando dati
        // nel formato tipico di una form HTML
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // 9. Permetto l'invio dei cookie (necessario per sessioni PHP)
        // Serve per mantenere l'utente loggato
        xhr.withCredentials = true;

        // 10. Definisco la funzione che gestisce la risposta del server
        xhr.onreadystatechange = function(){

            // Controllo se la richiesta è completata
            if(xhr.readyState === XMLHttpRequest.DONE){

                // Controllo se la risposta è andata a buon fine
                if(xhr.status === 200){

                    try {

                        // 11. Converto la risposta JSON in oggetto JavaScript
                        const response = JSON.parse(xhr.responseText);

                        // 12. Se il server indica che l'utente non è loggato
                        if(response.status === 'not_logged'){

                            // Reindirizzo alla pagina di login
                            window.location.href = '/Nexus-Space/pages/login.php';
                            return;
                        }

                        // 13. Se tutto è ok:
                        // Pulisco il campo input
                        commentInput.value = '';

                        // Ricarico i commenti per aggiornare il modal
                        loadComments(currentOperaId);

                    } catch(e){

                        // Se il JSON non è valido
                        console.error('Errore parsing JSON dopo invio commento:', e);
                    }

                } else {

                    // Se il server restituisce errore HTTP
                    console.error('Errore AJAX invio commento:', xhr.status);
                }
            }
        };

        // 14. Invio i dati al server
        // encodeURIComponent evita problemi con caratteri speciali
        xhr.send(
            'id_opera=' + encodeURIComponent(currentOperaId) + 
            '&commento=' + encodeURIComponent(commentText)
        );
    });
}

// --- GESTIONE LIKE ---
// Seleziono tutti i bottoni con classe btn-like
document.querySelectorAll('.btn-like').forEach(button => {

    // Aggiungo evento click
    button.addEventListener('click', function() {

        // 1. Recupero ID dell'opera dal data-id
        const idOpera = this.dataset.id;

        // 2. Recupero l'elemento HTML che contiene il numero di like
        const countElem = document.getElementById(`like-count-${idOpera}`);

        // 3. Converto il testo in numero intero
        let count = parseInt(countElem.textContent);

        // --- CREAZIONE RICHIESTA AJAX POST ---
        const xhr = new XMLHttpRequest();

        // 4. Apro la richiesta verso lo script PHP che gestisce i like
        xhr.open('POST', '/Nexus-Space/actions/add_like.php', true);

        // 5. Imposto header per invio dati form
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // 6. Includo i cookie per mantenere la sessione utente
        xhr.withCredentials = true;

        // 7. Gestione risposta server
        xhr.onreadystatechange = function() {

            if(xhr.readyState === XMLHttpRequest.DONE){

                if(xhr.status === 200){

                    // Qui NON uso JSON, ma testo semplice
                    const response = xhr.responseText;

                    // Se utente non loggato → redirect
                    if(response === 'not_logged'){
                        window.location.href = '/Nexus-Space/pages/login.php';
                        return;
                    }

                    // Se il like è stato aggiunto
                    if(response === 'added'){

                        // Cambio icona
                        button.innerText = '♥';

                        // Aggiungo classe CSS per effetto visivo
                        button.classList.add('liked');

                        // Incremento contatore localmente
                        countElem.textContent = count + 1;
                    }

                    // Se il like è stato rimosso
                    if(response === 'removed'){

                        // Cambio icona
                        button.innerText = '♡';

                        // Rimuovo classe CSS
                        button.classList.remove('liked');

                        // Decremento contatore localmente
                        countElem.textContent = count - 1;
                    }

                } else {

                    // Se errore HTTP
                    console.error('Errore richiesta like:', xhr.status);
                }
            }
        };

        // 8. Invio ID dell'opera al server
        xhr.send('id_opera=' + encodeURIComponent(idOpera));
    });
});