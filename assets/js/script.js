// JavaScript untuk Dompet Sesat - Simple Version

// Filter kategori berdasarkan tipe (sederhana tanpa API)
function filterCategories(type) {
    const categorySelect = document.getElementById('category');
    
    if (!categorySelect) return;
    
    // Loop semua option dan hide/show berdasarkan tipe
    Array.from(categorySelect.options).forEach(option => {
        if (option.value === '') return; // Skip option pertama "Pilih kategori"
        
        const optionType = option.getAttribute('data-type');
        if (optionType === type) {
            option.style.display = 'block';
            option.disabled = false;
        } else {
            option.style.display = 'none';
            option.disabled = true;
        }
    });
    
    // Reset pilihan jika kategori yang dipilih tidak sesuai tipe
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    if (selectedOption && selectedOption.getAttribute('data-type') !== type) {
        categorySelect.selectedIndex = 0;
    }
}

// Event listeners saat DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Check for success/error messages from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showToast('Transaksi berhasil ditambahkan!', 'success');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (urlParams.get('error')) {
        const errorType = urlParams.get('error');
        let errorMessage = 'Terjadi kesalahan';
        
        switch(errorType) {
            case 'validation':
                errorMessage = 'Data tidak lengkap. Pastikan semua field terisi dengan benar.';
                break;
            case 'database':
                errorMessage = 'Gagal menyimpan ke database. Silakan coba lagi.';
                break;
        }
        
        showToast(errorMessage, 'danger');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // Filter kategori default untuk expense
    filterCategories('expense');
    
    // Minimal setup - hanya set redirect field saat modal dibuka
    const addModal = document.getElementById('addModal');
    if (addModal) {
        addModal.addEventListener('shown.bs.modal', function() {
            // Set redirect field
            const currentPage = window.location.pathname.split('/').pop();
            const redirectField = document.getElementById('redirect_to');
            if (redirectField) {
                redirectField.value = currentPage;
            }
            
            // Set tanggal hari ini jika kosong
            const dateField = document.querySelector('input[name="date"]');
            if (dateField && !dateField.value) {
                dateField.value = new Date().toISOString().split('T')[0];
            }
        });
    }
    
    // Auto-submit filter form saat select berubah
    document.querySelectorAll('select[name="type"], select[name="month"], select[name="year"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Format input angka dengan pemisah ribuan
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters except decimal point
            let value = this.value.replace(/[^\d]/g, '');
            
            // Add thousand separators
            if (value.length > 3) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            
            // Update display (not the actual value)
            this.setAttribute('data-display', value);
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+N untuk tambah transaksi baru
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const addModal = document.getElementById('addModal');
            if (addModal) {
                new bootstrap.Modal(addModal).show();
            }
        }
        
        // Esc untuk tutup modal
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        }
        
        // Ctrl+F untuk focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }
    });
    
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Smooth scroll untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Biarkan HTML5 validation bekerja secara default
    
    // Real-time validation
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Quick add function untuk dashboard - with category selection
function quickAdd(category, amount) {
    // Gunakan Bootstrap default behavior
    const modalTrigger = document.querySelector('[data-bs-target="#addModal"]');
    if (modalTrigger) {
        modalTrigger.click();
        
        // Set values setelah modal terbuka
        setTimeout(() => {
            document.getElementById("expense").checked = true;
            document.getElementById("amount").value = amount;
            document.getElementById("description").value = category;
            
            // Auto-select kategori yang sesuai
            const categorySelect = document.getElementById("category");
            if (categorySelect) {
                // Mapping kategori untuk mencocokkan nama tombol dengan kategori database
                const categoryMapping = {
                    'makan': ['makan', 'makanan', 'food'],
                    'jajan': ['jajan', 'snack', 'cemilan'],
                    'transport': ['transport', 'transportasi', 'ojek', 'bus', 'angkot']
                };
                
                const searchCategory = category.toLowerCase();
                let found = false;
                
                // Cari exact match dulu
                Array.from(categorySelect.options).forEach(option => {
                    if (!found && option.value.toLowerCase() === searchCategory) {
                        categorySelect.value = option.value;
                        found = true;
                    }
                });
                
                // Kalau tidak ada exact match, cari berdasarkan mapping
                if (!found) {
                    Array.from(categorySelect.options).forEach(option => {
                        if (!found) {
                            const optionText = option.text.toLowerCase();
                            const optionValue = option.value.toLowerCase();
                            
                            // Cek apakah ada kata yang cocok
                            if (optionText.includes(searchCategory) || 
                                optionValue.includes(searchCategory) ||
                                (categoryMapping[searchCategory] && 
                                 categoryMapping[searchCategory].some(keyword => 
                                    optionText.includes(keyword) || optionValue.includes(keyword)))) {
                                categorySelect.value = option.value;
                                found = true;
                            }
                        }
                    });
                }
            }
        }, 500);
    }
}

// Utility functions
function formatRupiah(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID');
}

function formatTanggal(tanggal) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    };
    return new Date(tanggal).toLocaleDateString('id-ID', options);
}

// Show loading state
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Show toast notification
function showToast(message, type = 'success') {
    // Create toast element if not exists
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    
    // Determine icon and title based on type
    let icon, title, bgClass;
    switch(type) {
        case 'success':
            icon = 'check-circle-fill';
            title = 'Berhasil';
            bgClass = 'bg-success';
            break;
        case 'danger':
        case 'error':
            icon = 'exclamation-triangle-fill';
            title = 'Error';
            bgClass = 'bg-danger';
            break;
        case 'warning':
            icon = 'exclamation-triangle-fill';
            title = 'Peringatan';
            bgClass = 'bg-warning';
            break;
        case 'info':
            icon = 'info-circle-fill';
            title = 'Info';
            bgClass = 'bg-info';
            break;
        default:
            icon = 'info-circle-fill';
            title = 'Notifikasi';
            bgClass = 'bg-primary';
    }
    
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header ${bgClass} text-white">
                <i class="bi bi-${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'success' ? 3000 : 5000 // Error messages stay longer
    });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Berhasil disalin ke clipboard');
    }).catch(function() {
        showToast('Gagal menyalin ke clipboard', 'danger');
    });
}

// Debounce function untuk search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search dengan debounce
const debouncedSearch = debounce(function(searchTerm) {
    // Implement search logic here
    console.log('Searching for:', searchTerm);
}, 300);

// Event listener untuk search input
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            debouncedSearch(this.value);
        });
    }
});

// Local storage helpers
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (e) {
        console.error('Error saving to localStorage:', e);
    }
}

function getFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('Error reading from localStorage:', e);
        return null;
    }
}

// Save form data to localStorage (for recovery)
function saveFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    saveToLocalStorage(`form_${formId}`, data);
}

// Restore form data from localStorage
function restoreFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const data = getFromLocalStorage(`form_${formId}`);
    if (!data) return;
    
    Object.keys(data).forEach(key => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            field.value = data[key];
        }
    });
}

// Clear saved form data
function clearFormData(formId) {
    localStorage.removeItem(`form_${formId}`);
}

// Export functions for global use
window.DompetSesat = {
    filterCategories,
    formatRupiah,
    formatTanggal,
    showLoading,
    showToast,
    confirmAction,
    copyToClipboard,
    saveFormData,
    restoreFormData,
    clearFormData
};