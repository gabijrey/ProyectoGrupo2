document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('user-search-input');
    const limitSelect = document.getElementById('user-limit-select');
    const resultsContainer = document.getElementById('user-results-container');
    const paginationContainer = document.getElementById('user-pagination');

    let currentPage = 1;
    let debounceTimer;

    const fetchUsers = () => {
        const query = searchInput.value.trim();
        const limit = limitSelect.value;
        
        // Mostrar esqueleto o loading
        resultsContainer.innerHTML = '<div class="col-span-full flex justify-center py-10"><div class="animate-spin rounded-full h-12 w-12 border-b-4 border-yellow-500"></div></div>';

        fetch(`../buscar_usuarios.php?q=${encodeURIComponent(query)}&page=${currentPage}&limit=${limit}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                renderUsers(data.users);
                renderPagination(data.total_pages, data.current_page);
            })
            .catch(err => {
                console.error("Error al buscar usuarios:", err);
                resultsContainer.innerHTML = '<div class="col-span-full text-center text-rose-800 font-bold p-10 uppercase font-comic text-2xl">Error al cargar usuarios</div>';
            });
    };

    const renderUsers = (users) => {
        resultsContainer.innerHTML = '';
        
        if (!users || users.length === 0) {
            resultsContainer.innerHTML = '<div class="col-span-full text-center text-neutral-500 font-bold p-10 uppercase font-comic text-2xl">No se encontraron usuarios</div>';
            return;
        }

        users.forEach(user => {
            const card = document.createElement('a');
            card.href = `../sesion/perfilUsuario.php?usuario=${encodeURIComponent(user.nombre)}`;
            card.className = "bg-white border-4 border-black p-4 flex items-center gap-4 hover:bg-yellow-400 transition-all hover:-translate-y-1 shadow-[4px_4px_0_0_black] hover:shadow-[8px_8px_0_0_black] group";
            
            const imgHtml = user.img_perfil 
                ? `<img src="../${user.img_perfil}" alt="${user.nombre}" class="w-16 h-16 rounded-full border-2 border-black object-cover bg-neutral-200">`
                : `<div class="w-16 h-16 rounded-full border-2 border-black bg-rose-800 flex items-center justify-center font-comic text-2xl text-white uppercase">${user.nombre.substring(0, 1)}</div>`;

            card.innerHTML = `
                ${imgHtml}
                <div class="flex-1 min-w-0">
                    <h3 class="font-comic text-xl text-black uppercase truncate flex items-center gap-1" title="${user.nombre}">
                        ${user.nombre}
                        ${(user.rol == 1 || user.rol == 2) ? `
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-1 animate-pulse ${user.rol == 1 ? 'text-yellow-400' : 'text-cyan-400'} drop-shadow-[1px_1px_0_black]" title="${user.rol == 1 ? 'Premium' : 'Autor'}">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                            </svg>
                        ` : ''}
                    </h3>
                    <p class="text-xs font-bold text-neutral-600 uppercase tracking-wider">${user.total_resenas} RESEÑAS</p>
                </div>
                <div class="text-black font-black group-hover:translate-x-1 transition-transform shrink-0">➔</div>
            `;
            resultsContainer.appendChild(card);
        });
    };

    const renderPagination = (totalPages, page) => {
        paginationContainer.innerHTML = '';
        currentPage = page;
        
        if (totalPages <= 1) return;

        // Botón Anterior
        const prevBtn = document.createElement('button');
        prevBtn.innerText = 'PREV';
        prevBtn.disabled = currentPage === 1;
        prevBtn.className = `font-comic text-xl px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] transition-all ${currentPage === 1 ? 'bg-neutral-600 cursor-not-allowed opacity-50 text-neutral-400' : 'bg-yellow-500 text-white hover:bg-black hover:text-yellow-500 active:translate-x-1 active:translate-y-1 active:shadow-none'}`;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                fetchUsers();
            }
        };
        paginationContainer.appendChild(prevBtn);

        // Info de página
        const info = document.createElement('span');
        info.innerText = `${currentPage} / ${totalPages}`;
        info.className = "font-comic text-2xl text-white mx-4 bg-black px-6 py-1 border-4 border-white shadow-[6px_6px_0_0_black] -rotate-1";
        paginationContainer.appendChild(info);

        // Botón Siguiente
        const nextBtn = document.createElement('button');
        nextBtn.innerText = 'NEXT';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.className = `font-comic text-xl px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] transition-all ${currentPage === totalPages ? 'bg-neutral-600 cursor-not-allowed opacity-50 text-neutral-400' : 'bg-rose-800 text-white hover:bg-black hover:text-rose-800 active:translate-x-1 active:translate-y-1 active:shadow-none'}`;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                fetchUsers();
            }
        };
        paginationContainer.appendChild(nextBtn);
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            fetchUsers();
        }, 400);
    });

    limitSelect.addEventListener('change', () => {
        currentPage = 1;
        fetchUsers();
    });

    // Carga inicial
    fetchUsers();
});
