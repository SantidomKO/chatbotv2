(() => {
    const endpoint = 'index.php?controller=conocimiento&action=';
    const categories = document.getElementById('categories');
    const intents = document.getElementById('intents');
    const detail = document.getElementById('detail');
    const categoryForm = document.getElementById('categoryForm');
    const categoryModal = document.getElementById('modalCategoria');
    const categoryModalForm = document.getElementById('categoryModalForm');
    const btnCreateIntent = document.getElementById('btnCreateIntent');

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[char]));

    async function get(action, id) {
        const response = await fetch(endpoint + action + (id ? `&id=${encodeURIComponent(id)}` : ''));
        if (!response.ok) throw new Error('No se pudo cargar la información.');
        return response.json();
    }

    async function post(action, data) {
        const response = await fetch(endpoint + action, { method: 'POST', body: data });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.error || 'La operación no se pudo completar.');
        return result;
    }

    categories.addEventListener('click', async (event) => {
        const editButton = event.target.closest('.btn-edit-categoria');
        if (editButton) {
            document.getElementById('categoryId').value = editButton.dataset.categoryId;
            document.getElementById('categoryName').value = editButton.dataset.categoryName || '';
            document.getElementById('categoryDescription').value = editButton.dataset.categoryDescription || '';
            document.getElementById('modalCategoriaTitle').textContent = 'Editar Categoría';
            categoryModal.hidden = false;
            categoryModal.style.display = 'flex';
            return;
        }

        const deleteButton = event.target.closest('.btn-delete-categoria');
        if (deleteButton) {
            if (!window.confirm('¿Seguro que deseas eliminar esta categoría?')) return;
            const data = new FormData();
            data.append('id', deleteButton.dataset.categoryId);
            try {
                await post('eliminarCategoria', data);
                window.location.reload();
            } catch (error) {
                window.alert(error.message);
            }
            return;
        }

        const button = event.target.closest('[data-category-id]');
        if (!button) return;
        intents.innerHTML = '<li>Cargando...</li>';
        detail.innerHTML = '<p>Selecciona un intent.</p>';
        try {
            const data = await get('intents', button.dataset.categoryId);
            btnCreateIntent.hidden = false;
            intents.innerHTML = data.length
                ? data.map((intent) => `<li><button type="button" data-intent-id="${intent.id}">${escapeHtml(intent.nombre_intent)}</button></li>`).join('')
                : '<li>No hay intents en esta categoría.</li>';
        } catch (error) {
            intents.innerHTML = `<li>${escapeHtml(error.message)}</li>`;
        }
    });

    intents.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-intent-id]');
        if (!button) return;
        detail.innerHTML = '<p>Cargando...</p>';
        try {
            const data = await get('detalle', button.dataset.intentId);
            detail.innerHTML = `
                <h3>${escapeHtml(data.intent.nombre)}</h3>
                <p>${escapeHtml(data.intent.descripcion || 'Sin descripción')}</p>
                <h4>Frases de entrenamiento</h4>
                <ul>${data.frases.length ? data.frases.map((item) => `<li>${escapeHtml(item.texto)} <button type="button" data-delete-frase="${item.id}">Eliminar</button></li>`).join('') : '<li>No hay frases.</li>'}</ul>
                <form data-form="frase" data-intent-id="${data.intent.id}"><input name="texto" placeholder="Nueva frase" required><button type="submit">Agregar frase</button></form>
                <h4>Respuestas</h4>
                <ul>${data.respuestas.length ? data.respuestas.map((item) => `<li>${escapeHtml(item.texto)} <button type="button" data-delete-respuesta="${item.id}">Eliminar</button></li>`).join('') : '<li>No hay respuestas.</li>'}</ul>
                <form data-form="respuesta" data-intent-id="${data.intent.id}"><input name="texto" placeholder="Nueva respuesta" required><button type="submit">Agregar respuesta</button></form>`;
        } catch (error) {
            detail.innerHTML = `<p>${escapeHtml(error.message)}</p>`;
        }
    });

    detail.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-form]');
        if (!form) return;
        event.preventDefault();
        const data = new FormData(form);
        data.append('intent_id', form.dataset.intentId);
        try {
            await post(form.dataset.form === 'frase' ? 'guardarFrase' : 'guardarRespuesta', data);
            form.reset();
            document.querySelector(`[data-intent-id="${form.dataset.intentId}"]`).click();
        } catch (error) {
            window.alert(error.message);
        }
    });

    detail.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-delete-frase], [data-delete-respuesta]');
        if (!button) return;
        const data = new FormData();
        data.append('id', button.dataset.deleteFrase || button.dataset.deleteRespuesta);
        try {
            await post(button.dataset.deleteFrase ? 'eliminarFrase' : 'eliminarRespuesta', data);
            button.closest('li').remove();
        } catch (error) {
            window.alert(error.message);
        }
    });

    document.getElementById('newCategory').addEventListener('click', () => {
        categoryModalForm.reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('modalCategoriaTitle').textContent = 'Crear Categoría';
        categoryModal.hidden = false;
        categoryModal.style.display = 'flex';
    });

    categoryForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await post('guardarCategoria', new FormData(categoryForm));
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
        }
    });

    categoryModalForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const data = new FormData(categoryModalForm);
        const action = data.get('id') ? 'actualizarCategoria' : 'guardarCategoria';
        try {
            await post(action, data);
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
        }
    });

    ['closeCategoryModal', 'cancelCategory'].forEach((id) => {
        document.getElementById(id).addEventListener('click', () => {
            categoryModal.hidden = true;
            categoryModal.style.display = 'none';
        });
    });

    document.getElementById('btnRetrainRasa').addEventListener('click', async () => {
        const status = document.getElementById('retrainStatus');
        status.textContent = 'Iniciando...';
        try {
            const result = await post('retrain', new FormData());
            status.textContent = result.success ? 'Entrenamiento completado.' : 'Error en el entrenamiento.';
        } catch (error) {
            status.textContent = error.message;
        }
    });
})();
