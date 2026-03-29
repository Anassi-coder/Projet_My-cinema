const API_URL = window.location.origin + "/my_cinema/backend/index.php";

let currentSection = 'movies';
let currentPage = 1;
const itemsPerPage = 5;
let allData = [];

const modalElement = document.getElementById('entityModal');
const bootstrapModal = new bootstrap.Modal(modalElement);
const entityForm = document.getElementById('entity-form');

// NAVIGATION ET CHARGEMENT

function showSection(section) {
    currentSection = section;
    currentPage = 1;

    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if (window.event && window.event.currentTarget) {
        window.event.currentTarget.classList.add('active');
    }

    const titles = { 
        'movies': 'Gestion des Films', 
        'rooms': 'Gestion des Salles', 
        'shows': 'Planning des Séances' 
    };
    document.getElementById('section-title').innerText = titles[section];

    const menu = document.getElementById('sidebarMenu');
    if (window.innerWidth < 768 && menu.classList.contains('show')) {
        const bsCollapse = bootstrap.Collapse.getInstance(menu) || new bootstrap.Collapse(menu);
        bsCollapse.hide();
    }

    loadData();
}

async function loadData() {
    try {
        const response = await fetch(`${API_URL}?resource=${currentSection}`);
        const result = await response.json();
        
        if (Array.isArray(result)) {
            allData = result;
            renderTable();
            updateStats();
        } else {
            console.error("Le serveur a renvoyé une erreur :", result);
            allData = []; 
            renderTable();
            showAlert(result.error || "Erreur format de données", "danger");
        }
    } catch (error) {
        console.error("Erreur de chargement:", error);
        showAlert("Impossible de charger les données", "danger");
    }
}

// AFFICHAGE DES TABLEAUX

function renderTable() {
    const tbody = document.getElementById('main-tbody');
    const theadRow = document.getElementById('table-head-row'); // On cible la ligne d'en-tête
    tbody.innerHTML = '';

    // --- MISE À JOUR DE L'EN-TÊTE ---
    if (currentSection === 'movies') {
        theadRow.innerHTML = `
            <th>Titre</th>
            <th>Genre</th>
            <th>Durée</th>
            <th class="text-end">Actions</th>
        `;
    } else if (currentSection === 'rooms') {
        theadRow.innerHTML = `
            <th>Salle</th>
            <th>Type de salle</th>
            <th>Places</th>
            <th class="text-end">Actions</th>
        `;
    } else if (currentSection === 'shows') {
        theadRow.innerHTML = `
            <th>Film</th>
            <th>Salle</th>
            <th>Date & Heure</th>
            <th class="text-end">Actions</th>
        `;
    }

    // --- GÉNÉRATION DU CONTENU (Inchangé) ---
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = allData.slice(startIndex, endIndex);

    paginatedData.forEach(item => {
        let row = `<tr>`;
        if (currentSection === 'movies') {
            const publicRoot = window.location.origin + "/my_cinema/backend/public/";
            let cleanPath = item.image_url ? item.image_url.replace(/^\/+/, '') : null;
            const posterPath = cleanPath ? (publicRoot + cleanPath) : 'https://placehold.co/50x75?text=No+Poster';
            
            row += `
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${posterPath}" class="me-3 shadow-sm" style="width: 45px; height: 65px; object-fit: cover; border: 1px solid;" onerror="this.src='https://placehold.co/50x75?text=Error'">
                        <strong>${item.title}</strong>
                    </div>
                </td>
                <td><span class="badge bg-secondary">${item.genre || 'N/A'}</span></td>
                <td>${item.duration} min</td>
            `;
        } else if (currentSection === 'rooms') {
            row += `
                <td>${item.name}</td>
                <td><span class="badge bg-light text-dark border">${item.type}</span></td>
                <td>${item.capacity} places</td>
            `;
        } else if (currentSection === 'shows') {
            row += `
                <td><strong>${item.movie_title}</strong></td>
                <td><span class="badge bg-info text-dark">${item.room_name}</span></td>
                <td><i class="far fa-clock me-1"></i> ${new Date(item.start_time).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })}</td>
            `;
        }

        row += `
            <td class="text-end">
                <button class="btn btn-sm btn-outline-dark rounded-0" onclick="editItem(${item.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-0" onclick="deleteItem(${item.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        tbody.innerHTML += row;
    });

    updatePaginationButtons();
}

// ACTIONS CRUD

async function deleteItem(id) {
    if (!confirm("Voulez-vous vraiment supprimer cet élément ?")) return;

    try {
        const response = await fetch(`${API_URL}?resource=${currentSection}&id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if (response.ok) {
            showAlert(result.message || "Suppression réussie", "success");
            loadData();
        } else {
            showAlert(result.message || result.error || "Action impossible", "danger");
        }
    } catch (error) {
        showAlert("Erreur de connexion au serveur", "danger");
    }
}

entityForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(entityForm);

    try {
        const response = await fetch(`${API_URL}?resource=${currentSection}`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (response.ok) {
            bootstrapModal.hide();
            showAlert(result.message, "success");
            loadData();
        } else {
            showAlert(result.message || result.error || "Erreur lors de l'enregistrement", "danger");
        }
    } catch (error) {
        showAlert("Erreur de communication avec le serveur", "danger");
    }
};

// MODALE ET FORMULAIRES

let editId = null;

async function openModal(isEdit = false) {
    const fieldsContainer = document.getElementById('form-fields');
    
    if (!isEdit) {
        editId = null;
        entityForm.reset();
        const preview = document.getElementById('image-preview');
        if (preview) preview.classList.add('d-none');
    }

    if (currentSection === 'movies') {
        fieldsContainer.innerHTML = `
            <input type="hidden" name="id" value="${editId || ''}">
            <div class="col-12"><label class="form-label">Titre</label><input type="text" name="title" class="form-control" required></div>
            <div class="col-12">
                <label class="form-label">Affiche du film</label>
                <div class="mb-2"><img id="image-preview" src="#" class="rounded shadow-sm d-none" style="max-height: 120px;"></div>
                <input type="file" name="poster" class="form-control" accept="image/*" onchange="previewImage(event)">
            </div>
            <div class="col-md-6"><label class="form-label">Durée (min)</label><input type="number" name="duration" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Genre</label><input type="text" name="genre" class="form-control"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        `;
    } else if (currentSection === 'rooms') {
        fieldsContainer.innerHTML = `
            <input type="hidden" name="id" value="${editId || ''}">
            <div class="col-12"><label class="form-label">Nom de la salle</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Capacité</label><input type="number" name="capacity" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="Standard">Standard</option>
                    <option value="3D">3D</option>
                    <option value="IMAX">IMAX</option>
                </select>
            </div>
        `;
    } else if (currentSection === 'shows') {
        const [resMovies, resRooms] = await Promise.all([
            fetch(`${API_URL}?resource=movies`),
            fetch(`${API_URL}?resource=rooms`)
        ]);
        const movies = await resMovies.json();
        const rooms = await resRooms.json();

        fieldsContainer.innerHTML = `
            <input type="hidden" name="id" value="${editId || ''}">
            <div class="col-12"><label class="form-label">Film</label>
                <select name="movie_id" class="form-select" required>
                    <option value="">-- Sélectionner un film --</option>
                    ${movies.map(m => `<option value="${m.id}">${m.title} (${m.duration} min)</option>`).join('')}
                </select>
            </div>
            <div class="col-12"><label class="form-label">Salle</label>
                <select name="room_id" class="form-select" required>
                    <option value="">-- Sélectionner une salle --</option>
                    ${rooms.map(r => `<option value="${r.id}">${r.name} (${r.type})</option>`).join('')}
                </select>
            </div>
            <div class="col-12"><label class="form-label">Date et Heure de début</label>
                <input type="datetime-local" name="start_time" class="form-control" required>
            </div>
        `;
    }
    bootstrapModal.show();
}

async function editItem(id) {
    const item = allData.find(i => i.id == id);
    if (item) {
        editId = id;
        await openModal(true);
        
        for (let key in item) {
            let input = entityForm.querySelector(`[name="${key}"]`);
            if (input) input.value = item[key];
        }
        
        // CORRECTION IMAGE DANS LA MODALE : On utilise le même chemin complet
        if (currentSection === 'movies' && item.image_url) {
            const preview = document.getElementById('image-preview');
            const baseUrl = window.location.origin + "/my_cinema/backend/public/";
            preview.src = baseUrl + item.image_url; 
            preview.classList.remove('d-none');
        }
    }
}

// UTILITAIRES

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = () => {
        const preview = document.getElementById('image-preview');
        preview.src = reader.result;
        preview.classList.remove('d-none');
    };
    if (event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alert-container');
    if (!container) return;
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }
    }, 6000);
}

function updatePaginationButtons() {
    const totalPages = Math.ceil(allData.length / itemsPerPage);
    const pageInfo = document.getElementById('page-info');
    if (pageInfo) pageInfo.innerText = `Page ${currentPage} sur ${totalPages || 1}`;
    document.getElementById('prev-page').disabled = (currentPage === 1);
    document.getElementById('next-page').disabled = (currentPage >= totalPages || totalPages === 0);
}

function changePage(direction) {
    const totalPages = Math.ceil(allData.length / itemsPerPage);
    if (currentPage + direction >= 1 && currentPage + direction <= totalPages) {
        currentPage += direction;
        renderTable();
    }
}

async function updateStats() {
    try {
        const [resM, resR, resS] = await Promise.all([
            fetch(`${API_URL}?resource=movies`),
            fetch(`${API_URL}?resource=rooms`),
            fetch(`${API_URL}?resource=shows`)
        ]);
        const m = await resM.json();
        const r = await resR.json();
        const s = await resS.json();
        document.getElementById('stat-movies-count').innerText = m.length;
        document.getElementById('stat-rooms-count').innerText = r.length;
        document.getElementById('stat-shows-count').innerText = s.length;
    } catch (e) {}
}

window.onload = loadData;