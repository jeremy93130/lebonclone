// On va retirer les messages informatifs au bout de 5 secondes

setTimeout(() => {
    document.querySelectorAll('.alert-messages').forEach(element => element.remove());
}, 5000);


// Préparation requête ajax 
let buttons = document.querySelectorAll('.addToCart');
let span = document.createElement('span');
let msgTimeout = null;

async function addToCart(id) {

    try {
        span.textContent = '';

        const res = await fetch('/addCart/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ qty: 1 })
        });

        if (!res.ok) {
            throw new Error("Something went wrong");
        }

        const datas = await res.json();

        if (datas.length) {
            span.textContent = datas.length;
            span.style.color = "red";
        }

        if (datas.nb) {
            document.querySelector('#nbProd').textContent = datas.nb;
            document.querySelector('#nbProd').style.display = 'inline';
            span.textContent = "Your product has been added";
            span.style.color = "green";
        }

        buttons.forEach(button => {
            if (button.id === id) {
                button.after(span);
            }
        })

        if (msgTimeout) {
            clearTimeout(msgTimeout);
        }

        msgTimeout = setTimeout(() => {
            span.remove();
        }, 4000)
    } catch (error) {
        console.error(error);
    }
}

buttons.forEach(button => {
    button.addEventListener('click', function () {
        addToCart(button.id);
    })
})


let buttonsSupp = document.querySelectorAll('.delete-button');

async function deleteProductCart(id) {
    try {
        const res = await fetch('/delete/' + id);

        if (!res.ok) {
            throw new Error("Oops, something went wrong");
        }
        const data = await res.json();

        buttonsSupp.forEach(button => {
            if (button.getAttribute('data-id') === id) {

                if (data.nb === 0) {
                    document.querySelector('table').remove();
                    document.querySelector('#nbProd').style.display = 'none';

                    let empty = document.createElement('h2');
                    empty.textContent = "Your cart is empty !";

                    document.querySelector('.container').append(empty);
                    return;
                }
                document.querySelector('#total-cart').textContent = data.total;
                document.querySelector('#nbProd').textContent = data.nb;
                button.parentElement.parentElement.remove();
            }
        })
    } catch (error) {
        console.error(error);
    }
}


buttonsSupp.forEach(button => {
    button.addEventListener('click', function () {
        deleteProductCart(button.getAttribute('data-id'));
    })
});



// Supprimer Annonce

let delButton = document.querySelectorAll(".delete-ad");

async function deleteAd(id) {
    try {

        const res = await fetch('/delete-ad/' + id)

        if (!res.ok) {
            throw new Error("Oops, something went wrong");
        }

        const data = await res.json();

        if (data.success) {
            const carta = document.querySelectorAll('.carta');

            carta.forEach(cart => {
                if (cart.querySelector('.delete-ad').getAttribute('data-id') === id) {
                    cart.remove();
                }
            })
        }
    } catch (error) {
        console.error(error);
    }
}

delButton.forEach(button => {
    button.addEventListener('click', function () {
        deleteAd(button.getAttribute('data-id'));
    })
})




// Search ad 
// =========== Etats pour debounce + annulation ==========

let timer = null; // Sert à attendre avant de lancer le fetch
let controller = null; // Sert à arrêter la requête en cours

// On récupère l'input
let search = document.querySelector("#search");
let list = document.createElement('div');


// Petit utilitaire d'affichage 

function setMsg(text, cls = '') {
    msg.classList = "info" + cls;
    msg.textContent = text;
}

function clearList() {
    list.innerHTML = '';
}

async function searchAd(value) {

    if (controller) {
        controller.abort();
    }

    controller = new AbortController();
    try {
        console.log(value);
        const res = await fetch('/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ value }),
            signal: controller.signal
        });

        if (!res.ok) {
            throw new Error("Something went wrong");
        }

        const datas = await res.json();

        console.log(datas);
    } catch (error) {
        console.error(error);
    }
}

search.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length === 0) {
        if (controller) {
            controller.abort();
        }
        setMsg("");
        clearList();
        return;
    }

    if (q.length < MIN) {
        // On écrit un message qui dit qu'il nous manque tant de caractères pour lancer la recherche
        setMsg(`You must write ${MIN - q.length} letters to search`);
        clearList();
        return;
    }

    searchAd(search.value);
});