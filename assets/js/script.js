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
    
    // Filter kategori default untuk expense
    filterCategories('expense');
    
    // Event listener untuk perubahan tipe transaksi
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            filterCategories(this.value);
        });
    });
    
    // Inisialisasi manual modal Bootstrap
    const addModal = document.getElementById('addModal');
    if (addModal) {
        // Pastikan modal bisa dibuka
        document.querySelectorAll('[data-bs-target="#addModal"]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(addModal);
                modal.show();
                
                // Reset form dan filter kategori
                setTimeout(() => {
                    filterCategories('expense');
                    document.getElementById('expense').checked = true;
                }, 100);
            });
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
                bootstrap.Modal.getInstance(modal)?.hide();
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
    
    // Form validation enhancement
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Focus pada field pertama yang error
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    });
    
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
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header bg-${type} text-white">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
                <strong class="me-auto">${type === 'success' ? 'Berhasil' : 'Error'}</strong>
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
    const toast = new bootstrap.Toast(toastElement);
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