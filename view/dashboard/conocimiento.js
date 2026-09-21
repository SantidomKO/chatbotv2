document.addEventListener('DOMContentLoaded', function() {
    const categoriasContainer = document.getElementById('categoriasContainer');
    const intentsContainer = document.getElementById('intentsContainer');
    const detalleContainer = document.getElementById('detalleContainer');
    const btnCreateIntent = document.getElementById('btnCreateIntent');
    const btnCreateCategoria = document.getElementById('btnCreateCategoria');
    const modalIntent = document.getElementById('modalIntent');
    const modalCategoria = document.getElementById('modalCategoria');
    const modalFrase = document.getElementById('modalFrase');
    const formIntent = document.getElementById('formIntent');
    const formCategoria = document.getElementById('formCategoria');
    const formFrase = document.getElementById('formFrase');

    let selectedCategoryId = null;
    let selectedIntentId = null;

    [modalCategoria, modalIntent].forEach((modal) => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // ============ EVENT LISTENERS ============

    // Click en categoría
    categoriasContainer.addEventListener('click', (e) => {
        // Verificar si el clic fue en un botón de editar/eliminar
        if (e.target.closest('.btn-edit-categoria') || e.target.closest('.btn-delete-categoria')) {
            return; // Dejar que otros manejadores se encarguen
        }

        const item = e.target.closest('.categoria-item');
        if (!item) return;

        e.preventDefault();
        e.stopPropagation();

        const categoryId = item.dataset.categoryId;

        console.log('Categoría seleccionada:', categoryId); // Debug

        // Highlight selected category
        document.querySelectorAll('#categoriasContainer li').forEach(el => {
            el.style.background = 'rgba(0, 255, 255, 0.05)';
            el.style.border = '1px solid rgba(0, 255, 255, 0.1)';
        });
        item.style.background = 'rgba(0, 255, 255, 0.15)';
        item.style.border = '1px solid rgba(0, 255, 255, 0.3)';

        selectedCategoryId = categoryId;
        selectedIntentId = null;

        loadIntents(categoryId);
        clearDetalle();
        btnCreateIntent.style.display = 'inline-block';
    });

    // Click en intent
    intentsContainer.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit-intent');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            const item = editBtn.closest('.intent-item');
            const intentId = item.dataset.intentId;
            const name = item.dataset.intentNombre || '';
            const description = item.dataset.intentDescripcion || '';
            document.getElementById('intentId').value = intentId;
            document.getElementById('intentNombre').value = name;
            document.getElementById('intentDesc').value = description;
            document.getElementById('intentCategoria').value = selectedCategoryId || '';
            document.getElementById('modalIntentTitle').textContent = 'Editar Intent';
            formIntent.dataset.mode = 'edit';
            modalIntent.style.display = 'flex';
            return;
        }

        const deleteBtn = e.target.closest('.btn-delete-intent');
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();
            const item = deleteBtn.closest('.intent-item');
            const intentId = item.dataset.intentId;
            if (!confirm('¿Seguro que deseas eliminar este intent?')) return;

            const formData = new FormData();
            formData.append('id', intentId);

            fetch('index.php?controller=dashboard&action=deleteIntent', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadIntents(selectedCategoryId);
                    clearDetalle();
                } else {
                    alert(data.error || 'Error al eliminar el intent');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al eliminar el intent');
            });
            return;
        }

        const item = e.target.closest('.intent-item');
        if (!item) return;
        e.preventDefault();

        const intentId = item.dataset.intentId;

        document.querySelectorAll('.intent-item').forEach(el => el.classList.remove('active'));
        item.classList.add('active');

        selectedIntentId = intentId;
        loadIntentPhrases(intentId);
    });

    // Click en botón "Crear Categoría"
    btnCreateCategoria?.addEventListener('click', () => {
        document.getElementById('categoriaId').value = '';
        document.getElementById('categoriaNombre').value = '';
        document.getElementById('categoriaDescripcion').value = '';
        document.getElementById('modalCategoriaTitle').textContent = 'Crear Categoría';
        formCategoria.dataset.mode = 'create';
        modalCategoria.style.display = 'flex';
    });

    // Click en editar categoría
    categoriasContainer.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit-categoria');
        if (!editBtn) return;
        e.preventDefault();
        e.stopPropagation();

        document.getElementById('categoriaId').value = editBtn.dataset.categoryId;
        document.getElementById('categoriaNombre').value = editBtn.dataset.categoryName || '';
        document.getElementById('categoriaDescripcion').value = editBtn.dataset.categoryDescription || '';
        document.getElementById('modalCategoriaTitle').textContent = 'Editar Categoría';
        formCategoria.dataset.mode = 'edit';
        modalCategoria.style.display = 'flex';
    });

    // Click en eliminar categoría
    categoriasContainer.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.btn-delete-categoria');
        if (!deleteBtn) return;
        e.preventDefault();
        e.stopPropagation();

        const categoryId = deleteBtn.dataset.categoryId;
        if (!confirm('¿Seguro que deseas eliminar esta categoría?')) return;

        const formData = new FormData();
        formData.append('id', categoryId);

        fetch('index.php?controller=dashboard&action=deleteCategoria', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Error al eliminar la categoría');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al eliminar la categoría');
        });
    });

    // Submit del formulario Categoría
    formCategoria.addEventListener('submit', (e) => {
        e.preventDefault();
        const mode = formCategoria.dataset.mode || 'create';
        const action = mode === 'edit' ? 'updateCategoria' : 'createCategoria';

        const formData = new FormData(formCategoria);

        fetch(`index.php?controller=dashboard&action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                modalCategoria.style.display = 'none';
                window.location.reload();
            } else {
                alert(data.error || 'Error al guardar la categoría');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al guardar la categoría');
        });
    });

    // Click en botón "Crear Intent"
    btnCreateIntent.addEventListener('click', () => {
        document.getElementById('intentId').value = '';
        document.getElementById('intentNombre').value = '';
        document.getElementById('intentDesc').value = '';
        document.getElementById('intentCategoria').value = selectedCategoryId || '';
        document.getElementById('modalIntentTitle').textContent = 'Crear Intent';
        formIntent.dataset.mode = 'create';
        modalIntent.style.display = 'flex';
    });

    // Submit del formulario Intent
    formIntent.addEventListener('submit', (e) => {
        e.preventDefault();
        const mode = formIntent.dataset.mode || 'create';
        const action = mode === 'edit' ? 'updateIntent' : 'createIntent';
        
        const formData = new FormData(formIntent);
        
        fetch(`index.php?controller=dashboard&action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                modalIntent.style.display = 'none';
                formIntent.reset();
                if (selectedCategoryId) {
                    loadIntents(selectedCategoryId);
                }
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => console.error(err));
    });

    // ============ FUNCIONES PRINCIPALES ============

    function loadIntents(categoryId) {
        intentsContainer.innerHTML = '<p class="text-center">Cargando...</p>';
        
        console.log('Cargando intents para categoría:', categoryId); // Debug
        
        fetch(`index.php?controller=dashboard&action=getIntentsByCategory&id=${categoryId}`)
            .then(res => {
                console.log('Respuesta del servidor:', res); // Debug
                return res.json();
            })
            .then(intents => {
                console.log('Intents recibidos:', intents); // Debug
                
                if (intents.length === 0) {
                    intentsContainer.innerHTML = '<p class="text-center text-muted">No hay intents en esta categoría</p>';
                    return;
                }
                
                let html = '<ul class="intent-list" style="list-style: none; padding: 0; margin: 0;">';
                intents.forEach(intent => {
                    html += `
                        <li class="intent-item" data-intent-id="${intent.id}" data-intent-nombre="${escapeHtml(intent.nombre_intent)}" data-intent-descripcion="${escapeHtml(intent.descripcion || '')}" style="margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(0, 255, 255, 0.1); cursor: pointer;">
                            <div class="intent-main" style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="intent-link" style="color: #e0e0e0; text-decoration: none; font-weight: 500; flex: 1;">
                                    ${escapeHtml(intent.nombre_intent)}
                                </span>
                                <div class="intent-actions" style="display: flex; gap: 0.5rem;">
                                    <button type="button" class="btn btn-edit-intent" data-intent-id="${intent.id}" aria-label="Editar intent" style="background: none; border: none; color: #00ffff; cursor: pointer; font-size: 1rem;">✎</button>
                                    <button type="button" class="btn btn-delete-intent" data-intent-id="${intent.id}" aria-label="Eliminar intent" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1rem;">✕</button>
                                </div>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                intentsContainer.innerHTML = html;
            })
            .catch(err => {
                console.error('Error al cargar intents:', err); // Debug
                intentsContainer.innerHTML = '<p class="text-center text-danger">Error al cargar intents</p>';
            });
    }

    function loadIntentPhrases(intentId) {
        detalleContainer.innerHTML = '<p class="text-center">Cargando detalles...</p>';
        
        fetch(`index.php?controller=dashboard&action=getIntentDetail&id=${intentId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    detalleContainer.innerHTML = `<p class="text-center text-danger">Error: ${data.error}</p>`;
                    return;
                }
                
                const intent = data.intent;
                const frases = data.frases_entrenamiento || [];
                const respuestas = data.respuestas || [];
                
                let html = `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #00ffff; margin: 0 0 0.5rem 0;">${escapeHtml(intent.nombre_intent)}</h4>
                        <p style="color: #b0b0b0; margin: 0; font-size: 0.9rem;">${escapeHtml(intent.descripcion || 'Sin descripción')}</p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h5 style="color: #00ffff; margin: 0 0 1rem 0; font-size: 1rem;">Frases de Entrenamiento</h5>
                        <div id="frasesList">
                `;
                
                if (frases.length === 0) {
                    html += '<p style="color: #b0b0b0; padding: 1rem; text-align: center; background: rgba(0, 255, 255, 0.05); border-radius: 8px;">No hay frases de entrenamiento</p>';
                } else {
                    frases.forEach(frase => {
                        html += `
                            <div style="margin-bottom: 0.5rem; padding: 0.75rem; background: rgba(0, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(0, 255, 255, 0.1);" data-frase-id="${frase.id}">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1; color: #e0e0e0;">${escapeHtml(frase.texto_ejemplo)}</div>
                                    <div style="display: flex; gap: 0.5rem; margin-left: 1rem;">
                                        <button type="button" class="btn btn-edit-frase" data-frase-id="${frase.id}" data-frase-text="${escapeHtml(frase.texto_ejemplo)}" aria-label="Editar frase" style="background: none; border: none; color: #00ffff; cursor: pointer; font-size: 1rem;">✎</button>
                                        <button type="button" class="btn btn-delete-frase btn-eliminar-frase" data-frase-id="${frase.id}" aria-label="Eliminar frase" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1rem;">✕</button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                html += `
                        </div>
                        <form class="frase-add-form" data-intent-id="${intent.id}" style="margin-top: 1rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" class="frase-texto" placeholder="Agregar nueva frase..." required style="flex: 1; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;">
                                <button type="submit" style="padding: 0.5rem 1rem; background: rgba(0, 255, 255, 0.2); border: 1px solid rgba(0, 255, 255, 0.4); border-radius: 8px; color: #00ffff; cursor: pointer;">Agregar</button>
                            </div>
                        </form>
                    </div>

                    <div style="border-top: 1px solid rgba(0, 255, 255, 0.1); padding-top: 1.5rem;">
                        <h5 style="color: #00ffff; margin: 0 0 1rem 0; font-size: 1rem;">Respuestas</h5>
                        <div id="respuestasList">
                `;
                
                if (respuestas.length === 0) {
                    html += '<p style="color: #b0b0b0; padding: 1rem; text-align: center; background: rgba(0, 255, 255, 0.05); border-radius: 8px;">No hay respuestas configuradas</p>';
                } else {
                    respuestas.forEach(respuesta => {
                        const fecha = respuesta.fecha_actualizacion ? new Date(respuesta.fecha_actualizacion).toLocaleDateString('es-AR') : 'N/A';
                        const activaLabel = respuesta.activa ? '✓ Activa' : '✕ Inactiva';
                        const btnToggleLabel = respuesta.activa ? 'Desactivar' : 'Activar';
                        const btnToggleStyle = respuesta.activa ? 'background: rgba(255, 59, 48, 0.2); border: 1px solid rgba(255, 59, 48, 0.4); color: #ff3b30;' : 'background: rgba(32, 199, 122, 0.2); border: 1px solid rgba(32, 199, 122, 0.4); color: #20c77a;';
                        html += `
                            <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(0, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(0, 255, 255, 0.1);" data-respuesta-id="${respuesta.id}">
                                <div style="margin-bottom: 0.5rem;">
                                    <small style="color: #b0b0b0;">${activaLabel} · ${fecha}</small>
                                </div>
                                <textarea class="respuesta-texto" rows="2" style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0; margin-bottom: 0.5rem;">${escapeHtml(respuesta.texto_respuesta || '')}</textarea>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-sm btn-success btn-guardar-respuesta" data-respuesta-id="${respuesta.id}" style="padding: 0.3rem 0.6rem; background: rgba(32, 199, 122, 0.2); border: 1px solid rgba(32, 199, 122, 0.4); border-radius: 6px; color: #20c77a; cursor: pointer; font-size: 0.8rem;">Guardar</button>
                                    <button type="button" class="btn btn-sm btn-toggle-respuesta" data-respuesta-id="${respuesta.id}" style="padding: 0.3rem 0.6rem; ${btnToggleStyle} border-radius: 6px; cursor: pointer; font-size: 0.8rem;">${btnToggleLabel}</button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-respuesta" data-respuesta-id="${respuesta.id}" aria-label="Eliminar respuesta" style="padding: 0.3rem 0.6rem; background: rgba(255, 59, 48, 0.2); border: 1px solid rgba(255, 59, 48, 0.4); border-radius: 6px; color: #ff3b30; cursor: pointer; font-size: 0.8rem;">Eliminar</button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                html += `
                        </div>
                        <form class="respuesta-add-form" data-intent-id="${intent.id}" style="margin-top: 1rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" class="respuesta-texto-new" placeholder="Agregar nueva respuesta..." required style="flex: 1; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;">
                                <button type="submit" style="padding: 0.5rem 1rem; background: rgba(0, 255, 255, 0.2); border: 1px solid rgba(0, 255, 255, 0.4); border-radius: 8px; color: #00ffff; cursor: pointer;">Agregar</button>
                            </div>
                        </form>
                    </div>
                `;
                
                detalleContainer.innerHTML = html;
                attachDetalleEventListeners(intentId);
            })
            .catch(err => {
                console.error(err);
                detalleContainer.innerHTML = '<p class="text-center text-danger">Error al cargar detalles</p>';
            });
    }

    function attachDetalleEventListeners(intentId) {
        // Agregar frase
        document.querySelector('.frase-add-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const texto = document.querySelector('.frase-texto').value.trim();
            
            if (!texto) {
                alert('El texto de la frase no puede estar vacío');
                return;
            }
            
            const formData = new FormData();
            formData.append('intent_id', intentId);
            formData.append('texto_ejemplo', texto);
            
            fetch('index.php?controller=dashboard&action=addFrase', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.frase-texto').value = '';
                    loadIntentPhrases(intentId);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => console.error(err));
        });

        // Eliminar frase
        document.querySelectorAll('.btn-eliminar-frase').forEach(btn => {
            btn.addEventListener('click', function() {
                const fraseId = this.dataset.fraseId;
                if (!confirm('¿Estás seguro de que deseas eliminar esta frase?')) return;
                
                const formData = new FormData();
                formData.append('id', fraseId);
                
                fetch('index.php?controller=dashboard&action=deleteFrase', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadIntentPhrases(intentId);
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(err => console.error(err));
            });
        });

        // Abrir modal de edición para frase
        document.querySelectorAll('.btn-edit-frase').forEach(btn => {
            btn.addEventListener('click', function() {
                const fraseId = this.dataset.fraseId;
                const texto = this.dataset.fraseText || (this.closest('.frase-item')?.querySelector('.frase-text')?.textContent.trim() || '');
                document.getElementById('fraseId').value = fraseId;
                document.getElementById('fraseTexto').value = texto;
                document.getElementById('modalFraseTitle').textContent = 'Editar Frase';
                formFrase.dataset.mode = 'edit';
                modalFrase.show();
            });
        });

        // Submit del formulario de edición de frase (modal)
        formFrase?.addEventListener('submit', function(e) {
            e.preventDefault();
            const fraseId = document.getElementById('fraseId').value;
            const texto = document.getElementById('fraseTexto').value.trim();
            if (!texto) { alert('El texto de la frase no puede estar vacío'); return; }

            const formData = new FormData();
            formData.append('id', fraseId);
            formData.append('texto_ejemplo', texto);

            fetch('index.php?controller=dashboard&action=updateFrase', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    modalFrase.hide();
                    loadIntentPhrases(intentId);
                } else {
                    alert(data.error || 'Error al actualizar la frase');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al actualizar la frase');
            });
        });

        // Guardar respuesta
        document.querySelectorAll('.btn-guardar-respuesta').forEach(btn => {
            btn.addEventListener('click', function() {
                const respuestaId = this.dataset.respuestaId;
                const texto = this.closest('.respuesta-item').querySelector('.respuesta-texto').value.trim();
                
                if (!texto) {
                    alert('El texto de la respuesta no puede estar vacío');
                    return;
                }
                
                const formData = new FormData();
                formData.append('id', respuestaId);
                formData.append('texto_respuesta', texto);
                
                fetch('index.php?controller=dashboard&action=updateRespuesta', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Respuesta actualizada exitosamente');
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(err => console.error(err));
            });
        });

        // Toggle estado de respuesta
        document.querySelectorAll('.btn-toggle-respuesta').forEach(btn => {
            btn.addEventListener('click', function() {
                const respuestaId = this.dataset.respuestaId;
                
                const formData = new FormData();
                formData.append('id', respuestaId);
                
                fetch('index.php?controller=dashboard&action=toggleRespuesta', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadIntentPhrases(intentId);
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(err => console.error(err));
            });
        });

        // Eliminar respuesta
        document.querySelectorAll('.btn-eliminar-respuesta').forEach(btn => {
            btn.addEventListener('click', function() {
                const respuestaId = this.dataset.respuestaId;
                if (!confirm('¿Estás seguro de que deseas eliminar esta respuesta?')) return;

                const formData = new FormData();
                formData.append('id', respuestaId);

                fetch('index.php?controller=dashboard&action=deleteRespuesta', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadIntentPhrases(intentId);
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(err => console.error(err));
            });
        });

        // Agregar respuesta
        document.querySelector('.respuesta-add-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const texto = document.querySelector('.respuesta-texto-new').value.trim();
            
            if (!texto) {
                alert('El texto de la respuesta no puede estar vacío');
                return;
            }
            
            const formData = new FormData();
            formData.append('intent_id', intentId);
            formData.append('texto_respuesta', texto);
            
            fetch('index.php?controller=dashboard&action=addRespuesta', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.respuesta-texto-new').value = '';
                    loadIntentPhrases(intentId);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => console.error(err));
        });
    }

    function clearDetalle() {
        detalleContainer.innerHTML = '<p class="text-center text-muted mt-4">Selecciona un intent para ver detalles</p>';
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }

    // Retrain model handler
    const btnRetrain = document.getElementById('btnRetrainRasa');
    if (btnRetrain) {
        btnRetrain.addEventListener('click', function() {
            if (!confirm('Iniciar reentrenamiento del modelo Rasa? Esto puede tardar varios minutos.')) return;
            retrainModel();
        });
    }

    function retrainModel() {
        const statusEl = document.getElementById('retrainStatus');
        if (statusEl) statusEl.textContent = 'Iniciando...';
        if (btnRetrain) btnRetrain.disabled = true;

        fetch('index.php?controller=dashboard&action=retrain', { method: 'POST' })
            .then(async res => {
                const text = await res.text();
                if (!text) {
                    // Empty body
                    if (!res.ok) throw new Error('Empty response from server (status ' + res.status + ')');
                    return {};
                }
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.slice(0, 200));
                }
            })
            .then(data => {
                if (data.success) {
                    if (statusEl) statusEl.textContent = 'Entrenamiento completado. Modelo: ' + (data.model_file || 'ok');
                } else {
                    if (statusEl) statusEl.textContent = 'Error: ' + (data.error || 'ver logs');
                    console.error(data);
                }
            })
            .catch(err => {
                if (statusEl) statusEl.textContent = 'Error en la petición: ' + err.message;
                console.error(err);
            })
            .finally(() => { if (btnRetrain) btnRetrain.disabled = false; });
    }
});
