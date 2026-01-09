const PHP_API = '../../php/adminProducts.php';
let productes = [];
let editarIndex = -1;

// Ajax general
function enviarAjax(url, metode, data, callback) {
    const xhr = new XMLHttpRequest();

    xhr.onload = function () {
        console.log('Resposta del servidor:', xhr.responseText);
        if (xhr.status === 200) {
            try {
                const respuesta = JSON.parse(xhr.responseText);
                callback(respuesta);
            } catch (e) {
                console.error('Error al parsejar el JSON:', e);
                callback({ success: false, message: 'Error al processar resposta' });
            }
        } else {
            callback({ success: false, message: 'Error al servidor' + xhr.status });
        }
    };

    xhr.onerror = function () {
        console.error('Error de connexió');
        callback({ success: false, message: 'Error de connexió' });
    };

    xhr.open(metode, url, true);
    xhr.send(data);
}

// Cargar productes
function loadProducts() {
    enviarAjax(PHP_API + '?accion=listar', 'GET', null, function (data) {
        console.log('Productos recibidos:', data);
        if (data.success) {
            productes = data.products;
            showProducts();
        } else {
            productes = [];
            showProducts();
        }
    });
}

// Mostrar productes a la taula
function showProducts() {
    const tbody = document.querySelector('#products-table tbody');
    tbody.innerHTML = '';

    if (productes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">No hi ha prouctes</td></tr>';
        return;
    }

    productes.forEach((producte, index) => {
        const fila = tbody.insertRow();

        // Insertar imatge
        fila.insertCell().innerHTML = `<img src="../resources/images/products/${producte.imatge}" alt="${producte.nom}" style="width: 50px;">`;

        // Insertar dades
        fila.insertCell().textContent = producte.nom;
        fila.insertCell().textContent = producte.preu.toFixed(2) + ' €';
        fila.insertCell().textContent = producte.categoria;
        fila.insertCell().textContent = producte.descripció || 'Sense descripció';
        fila.insertCell().innerHTML = producte.oferta ? '<span style="color: green;">Sí</span>' : '<span style="color: red;">No</span>';

        // Horaris
        const horaris = producte.horario || [];
        const horariosTexto = horaris.map(h => {
            if (h === 'manana') return 'Matí';
            if (h === 'tarde') return 'Tarda';
            if (h === 'noche') return 'Nit';
            return h;
        }).join(', ');
        fila.insertCell().textContent = horariosTexto || 'No especificat';

        const accions = fila.insertCell();
        accions.innerHTML = `
        <div class="flex">
           <button onclick="prepareEdit(${index})" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></button>
            <button onclick="deleteProduct(${index})" class="btn-delete" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
        </div>`;
    });
}

// Preview img
document.getElementById('imatge').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        console.log('Imagen seleccionada:', file.name);
        const reader = new FileReader();
        reader.onload = function (event) {
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.style.cssText = 'max-width: 150px; margin-top: 10px; border: 2px solid #ddd; padding: 5px;';
                e.target.parentElement.appendChild(preview);
            }
            preview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Guardar producte
document.getElementById('product-form').addEventListener('submit', function (e) {
    e.preventDefault();

    console.log('Guardant producte...');
    console.log('Mode edició:', editarIndex >= 0 ? 'Editar' : 'Afegir');

    // Recollir dades
    const formData = new FormData(this);

    // Editar vs añadir
    if (editarIndex >= 0) {
        formData.append('accion', 'editar');
        formData.append('index', editarIndex);
    } else {
        formData.append('accion', 'añadir');
    }

    // Crear XMLHttpRequest
    const xhr = new XMLHttpRequest();

    xhr.onload = function () {
        console.log('Resposta:', xhr.responseText);

        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Dades parsejades:', data);

                if (data.success) {
                    loadProducts();
                    cleanForm();
                }
            } catch (e) {
                console.error('Error al parsejar resposta:', e);
            }
        } else {
            console.error('Error al guardar' + xhr.status);
        }
    };

    xhr.onerror = function () {
        console.error('Error de conexión');
    };

    xhr.open('POST', PHP_API, true);
    xhr.send(formData);
});

// Preparar per editar
function prepareEdit(index) {
    console.log('Editant producte:', index);
    editarIndex = index;
    const producte = productes[index];

    // reomplir form 
    document.getElementById('nom').value = producte.nom;
    document.getElementById('preu').value = producte.preu;
    document.getElementById('descripcio').value = producte.descripció || '';
    document.getElementById('categoria').value = producte.categoria;
    document.getElementById('oferta').checked = producte.oferta;

    // Marcar horaris
    const checkboxesSchedule = document.querySelectorAll('input[name="horario[]"]');
    checkboxesSchedule.forEach(checkbox => {
        checkbox.checked = producte.horario && producte.horario.includes(checkbox.value);
    });

    // Mostrar imatge
    let preview = document.getElementById('image-preview');
    if (!preview) {
        preview = document.createElement('img');
        preview.id = 'image-preview';
        preview.style.cssText = 'max-width: 150px; margin-top: 10px; border: 2px solid #ddd; padding: 5px;';
        document.getElementById('imatge').parentElement.appendChild(preview);
    }
    preview.src = '../resources/images/products/' + producte.imatge;

    // Cambiar el botón
    document.querySelector('.btn-save').textContent = 'Actualitzar Producte';
    document.getElementById('btn-cancel-edit').style.display = 'inline-block';

    // Añadir texto informativo
    let infoText = document.getElementById('info-imagen-edit');
    if (!infoText) {
        infoText = document.createElement('small');
        infoText.id = 'info-imagen-edit';
        infoText.style.display = 'block';
        infoText.style.marginTop = '5px';
        infoText.style.color = '#666';
        document.getElementById('imatge').parentElement.appendChild(infoText);
    }
    infoText.textContent = '* No seleccionis cap imatge per mantenir la actual';

    // Scroll!!!!!!!!!!!!!!!1
    document.getElementById('product-form').scrollIntoView({ behavior: 'smooth' });
}

// Eliminar producte
function deleteProduct(index) {
    if (!confirm('Segur que vols eliminar: ' + productes[index].nom + '?')) {
        return;
    }

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('index', index);

    const xhr = new XMLHttpRequest();

    xhr.onload = function () {
        console.log('Resposta eliminar:', xhr.responseText);
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    loadProducts();
                } else {
                    console.error(data.message, 'error');
                }
            } catch (e) {
                console.error('Error al processar respoesta', e);
            }
        } else {
            console.error('Error al eliminar');
        }
    };

    xhr.onerror = function () {
        console.error('Error de connexió');
    };

    xhr.open('POST', PHP_API, true);
    xhr.send(formData);
}

// Netejar form
function cleanForm() {
    document.getElementById('product-form').reset();
    editarIndex = -1;

    // Resetejar checkbox
    document.querySelectorAll('input[name="horario[]"]').forEach(cb => cb.checked = true);

    document.querySelector('.btn-save').textContent = 'Guardar Producte';
    document.getElementById('btn-cancel-edit').style.display = 'none';

    // Eliminar preview 
    const preview = document.getElementById('image-preview');
    if (preview) preview.remove();

    // Eliminar text
    const infoText = document.getElementById('info-imagen-edit');
    if (infoText) infoText.remove();
}


document.getElementById('btn-cancel-edit').addEventListener('click', cleanForm);


document.addEventListener('DOMContentLoaded', function () {
    loadProducts();
});