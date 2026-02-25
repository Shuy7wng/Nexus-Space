// --- Riferimenti agli elementi DOM ---
const modal = document.getElementById('comment-modal');       // Il div del modal commenti
const closeBtn = document.getElementById('close-modal');      // Pulsante X per chiudere il modal
const modalBody = document.getElementById('modal-comment-list'); // Div dove inserire i commenti
const commentForm = document.getElementById('modal-comment-form'); // Form per aggiungere commenti

let currentOperaId = null; // Tiene traccia dell'opera corrente selezionata

// --- FUNZIONE PER CARICARE I COMMENTI VIA AJAX ---
function loadComments(idOpera) {
    // Mostra messaggio di caricamento
    modalBody.innerHTML = '<p class="loading">Caricamento commenti...</p>';

    // Creazione richiesta XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `/Nexus-Space/actions/comments_handler.php?id_opera=${idOpera}`, true);

    xhr.onreadystatechange = function() {
        if(xhr.readyState === XMLHttpRequest.DONE) {
            if(xhr.status === 200) {
                try {
                    const comments = JSON.parse(xhr.responseText);

                    if(comments.length === 0){
                        modalBody.innerHTML = '<p class="inter">Nessun commento presente.</p>';
                    } else {
                        // Genero HTML dei commenti
                        modalBody.innerHTML = comments.map(c => 
                            `<div class="comment"><strong>${c.nickname}</strong>: ${c.commento}</div>`
                        ).join('');
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
document.querySelectorAll('.btn-comment').forEach(button => {
    button.addEventListener('click', function() {
        currentOperaId = this.dataset.id; // Salvo l'ID dell'opera selezionata
        modal.style.display = 'flex';    // Mostro il modal

        loadComments(currentOperaId);     // Carico i commenti via AJAX
    });
});

// --- CHIUSURA MODAL ---
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});
window.addEventListener('click', function(e) {
    if(e.target === modal){
        modal.style.display = 'none';
    }
});

// --- GESTIONE INVIO COMMENTI ---
if(commentForm){
    commentForm.addEventListener('submit', function(e){
        e.preventDefault();

        if(!currentOperaId) return;

        const commentInput = commentForm.querySelector('input[name="commento"]');
        const commentText = commentInput.value.trim();
        if(!commentText) return;

        // Creazione richiesta POST per inviare commento
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/Nexus-Space/actions/comments_handler.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.withCredentials = true;

        xhr.onreadystatechange = function(){
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    try {
                        const response = JSON.parse(xhr.responseText);

                        // Se utente non loggato
                        if(response.status === 'not_logged'){
                            window.location.href = '/Nexus-Space/pages/login.php';
                            return;
                        }

                        // Pulisco input e ricarico commenti
                        commentInput.value = '';
                        loadComments(currentOperaId);

                    } catch(e){
                        console.error('Errore parsing JSON dopo invio commento:', e);
                    }
                } else {
                    console.error('Errore AJAX invio commento:', xhr.status);
                }
            }
        };

        // Invio dati id_opera e commento
        xhr.send('id_opera=' + encodeURIComponent(currentOperaId) + 
                 '&commento=' + encodeURIComponent(commentText));
    });
}

// --- GESTIONE LIKE ---
document.querySelectorAll('.btn-like').forEach(button => {
    button.addEventListener('click', function() {
        const idOpera = this.dataset.id;
        const countElem = document.getElementById(`like-count-${idOpera}`);
        let count = parseInt(countElem.textContent);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/Nexus-Space/actions/add_like.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.withCredentials = true;

        xhr.onreadystatechange = function() {
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    const response = xhr.responseText;

                    if(response === 'not_logged'){
                        window.location.href = '/Nexus-Space/pages/login.php';
                        return;
                    }

                    if(response === 'added'){
                        button.innerText = '♥';
                        button.classList.add('liked');
                        countElem.textContent = count + 1;
                    }

                    if(response === 'removed'){
                        button.innerText = '♡';
                        button.classList.remove('liked');
                        countElem.textContent = count - 1;
                    }
                } else {
                    console.error('Errore richiesta like:', xhr.status);
                }
            }
        };

        xhr.send('id_opera=' + encodeURIComponent(idOpera));
    });
});